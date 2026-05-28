#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

port_in_use() {
  local p="$1"
  if command -v nc >/dev/null 2>&1; then
    nc -z 127.0.0.1 "$p" 2>/dev/null
  elif command -v lsof >/dev/null 2>&1; then
    lsof -nP -iTCP:"${p}" -sTCP:LISTEN >/dev/null 2>&1
  else
    (echo >/dev/tcp/127.0.0.1/"${p}") >/dev/null 2>&1
  fi
}

pick_port() {
  local want="$1"
  shift
  local try candidate
  for candidate in "$want" "$@"; do
    if ! port_in_use "$candidate"; then
      [[ "$candidate" != "$want" ]] && echo "Port ${want} busy; using ${candidate}" >&2
      echo "$candidate"
      return
    fi
  done
  echo "No free port (wanted ${want})" >&2
  exit 1
}

export HTTP_PORT="$(pick_port "${HTTP_PORT:-8080}" 8081 8082 18080 28080 38080)"
export HTTPS_PORT="$(pick_port "${HTTPS_PORT:-8443}" 8444 18443 28443 38443)"
while [[ "$HTTPS_PORT" == "$HTTP_PORT" ]]; do
  HTTPS_PORT="$(pick_port "$((HTTPS_PORT + 1))" "$((HTTPS_PORT + 2))" 18443 28443 38443)"
done

CERT_DIR=docker/nginx/certs
mkdir -p "$CERT_DIR"
if [[ ! -f "$CERT_DIR/dev.crt" || ! -f "$CERT_DIR/dev.key" ]]; then
  echo "=== Generate dev TLS certs (localhost) ==="
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$CERT_DIR/dev.key" \
    -out "$CERT_DIR/dev.crt" \
    -subj "/CN=localhost"
fi

COMPOSE=(docker compose -f compose.https.yaml)

"${COMPOSE[@]}" down -v 2>/dev/null || true

echo "=== 1. Start HTTPS stack (HTTP :${HTTP_PORT}, HTTPS :${HTTPS_PORT}) ==="
"${COMPOSE[@]}" up -d
sleep 2

echo ""
echo "=== 2. nginx -t ==="
"${COMPOSE[@]}" exec -T web nginx -t 2>&1 | grep -qi 'successful'

echo ""
echo "=== 3. Reload nginx ==="
"${COMPOSE[@]}" exec -T web nginx -s reload

echo ""
echo "=== 4. HTTPS response headers ==="
https_headers="$(curl -skI "https://127.0.0.1:${HTTPS_PORT}/index.php" | tr -d '\r')"
echo "$https_headers" | grep -iE '^(HTTP/|server:|strict-transport|x-content-type|x-frame|referrer-policy:)'
echo "$https_headers" | grep -qi '^server: nginx$'
echo "$https_headers" | grep -qi 'strict-transport-security:'
echo "$https_headers" | grep -qi 'x-content-type-options: nosniff'
echo "$https_headers" | grep -qi 'x-frame-options: sameorigin'
echo "$https_headers" | grep -qi 'referrer-policy: strict-origin-when-cross-origin'

echo ""
echo "=== 5. server_tokens off (no version in Server) ==="
server_line="$(echo "$https_headers" | grep -i '^server:')"
echo "$server_line"
echo "$server_line" | grep -qvE 'nginx/[0-9]'

echo ""
echo "=== 6. No HSTS on plain HTTP ==="
http_hsts="$(curl -sI "http://127.0.0.1:${HTTP_PORT}/" | grep -i strict-transport || true)"
if [[ -n "$http_hsts" ]]; then
  echo "Unexpected HSTS on HTTP: $http_hsts" >&2
  exit 1
fi
echo "no HSTS on HTTP (expected locally)"

echo ""
echo "=== 7. PHP route still works over HTTPS ==="
body="$(curl -fsSk "https://127.0.0.1:${HTTPS_PORT}/index.php")"
echo "$body" | head -2
echo "$body" | grep -qi 'FastCGI via'

echo ""
echo "=== 8. Headers on PHP location (inheritance check) ==="
php_headers="$(curl -skI "https://127.0.0.1:${HTTPS_PORT}/index.php" | tr -d '\r')"
echo "$php_headers" | grep -qi 'x-content-type-options: nosniff'
echo "$php_headers" | grep -qi 'strict-transport-security:'

"${COMPOSE[@]}" down -v

echo ""
echo "Demo OK: security headers + HSTS on HTTPS; server_tokens off; no HSTS on HTTP."
