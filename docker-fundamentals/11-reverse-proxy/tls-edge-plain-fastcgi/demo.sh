#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

HTTP_PORT="${HTTP_PORT:-8080}"
HTTPS_PORT="${HTTPS_PORT:-8443}"

pick_port() {
  local p="$1"
  if command -v nc >/dev/null 2>&1; then
    nc -z 127.0.0.1 "$p" 2>/dev/null && return 1
  fi
  return 0
}

if ! pick_port "$HTTP_PORT"; then
  HTTP_PORT=18080
fi
if ! pick_port "$HTTPS_PORT"; then
  HTTPS_PORT=18443
fi
export HTTP_PORT HTTPS_PORT

CERT_DIR=docker/nginx/certs
mkdir -p "$CERT_DIR"
if [[ ! -f "$CERT_DIR/dev.crt" || ! -f "$CERT_DIR/dev.key" ]]; then
  echo "=== Generate dev TLS certs (localhost) ==="
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$CERT_DIR/dev.key" \
    -out "$CERT_DIR/dev.crt" \
    -subj "/CN=localhost"
fi

echo "=== Start stack (HTTP :${HTTP_PORT}, HTTPS :${HTTPS_PORT}) ==="
docker compose up -d

echo "Waiting for nginx + FPM..."
for _ in $(seq 1 30); do
  if curl -fsS "http://127.0.0.1:${HTTP_PORT}/index.php" >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

echo ""
echo "=== 2. Plain HTTP at edge (FastCGI hop is still plain) ==="
http_body="$(curl -fsS "http://127.0.0.1:${HTTP_PORT}/index.php")"
echo "$http_body"
echo "$http_body" | grep -q 'HTTP_X_FORWARDED_PROTO=http'

echo ""
echo "=== 3. HTTPS terminates at nginx ==="
https_body="$(curl -fsSk "https://127.0.0.1:${HTTPS_PORT}/index.php")"
echo "$https_body"
echo "$https_body" | grep -q 'HTTP_X_FORWARDED_PROTO=https'

echo ""
echo "=== 4. FPM :9000 not published by this Compose stack ==="
app_ports="$(docker compose ps --format '{{.Service}} {{.Ports}}' | awk '$1=="app"{print $0; exit}')"
echo "$app_ports"
if echo "$app_ports" | grep -qE '0\.0\.0\.0:9000|127\.0\.0\.1:9000|\]->9000|:9000->'; then
  echo "Unexpected: app service publishes :9000 to the host" >&2
  exit 1
fi
echo "OK: app is 9000/tcp internal only (browsers use web :443/:80)"

echo ""
echo "=== 5. TLS handshake only at edge ==="
curl -fsSk -o /dev/null -w 'TLS verify (insecure dev cert): %{http_code}\n' \
  "https://127.0.0.1:${HTTPS_PORT}/"

docker compose down

echo ""
echo "Demo OK: TLS at web; plain FastCGI to app:9000; X-Forwarded-Proto set."
