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
  local candidate
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
  echo "=== Generate dev TLS certs ==="
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$CERT_DIR/dev.key" \
    -out "$CERT_DIR/dev.crt" \
    -subj "/CN=localhost"
fi

docker compose down -v 2>/dev/null || true

echo "=== 7. compose config ==="
docker compose config --quiet
services="$(docker compose config --services | tr '\n' ' ')"
echo "services: ${services}"
echo "$services" | grep -q 'web'
echo "$services" | grep -q 'app'

echo ""
echo "=== 8. Start stack ==="
docker compose up -d
sleep 2
docker compose ps
app_ports="$(docker compose ps --format '{{.Service}} {{.Ports}}' | awk '$1=="app"{print $0; exit}')"
echo "$app_ports"
echo "$app_ports" | grep -q '9000/tcp'
echo "$app_ports" | grep -qvE '0\.0\.0\.0:9000|127\.0\.0\.1:9000'

echo ""
echo "=== 9. nginx -t ==="
docker compose exec -T web nginx -t 2>&1 | grep -qi 'successful'

echo ""
echo "=== 10. HTTP smoke ==="
http_code="$(curl -fsS -o /dev/null -w '%{http_code}' "http://127.0.0.1:${HTTP_PORT}/index.php")"
echo "index.php → ${http_code}"
[[ "$http_code" == "200" ]]
http_body="$(curl -fsS "http://127.0.0.1:${HTTP_PORT}/index.php")"
echo "$http_body"
echo "$http_body" | grep -q 'ch11 proxy exercise ok'
echo "$http_body" | grep -q 'HTTP_X_FORWARDED_PROTO=http'
root_body="$(curl -fsS "http://127.0.0.1:${HTTP_PORT}/")"
echo "$root_body" | grep -q 'ch11 proxy exercise ok'

echo ""
echo "=== 11. HTTPS smoke + forwarded proto ==="
https_body="$(curl -fsSk "https://127.0.0.1:${HTTPS_PORT}/index.php")"
echo "$https_body"
echo "$https_body" | grep -q 'HTTP_X_FORWARDED_PROTO=https'

echo ""
echo "=== 12. Security headers on HTTPS ==="
https_headers="$(curl -skI "https://127.0.0.1:${HTTPS_PORT}/index.php" | tr -d '\r')"
echo "$https_headers" | grep -iE '^(server:|strict-transport|x-content-type|x-frame|referrer-policy:)'
echo "$https_headers" | grep -qi 'strict-transport-security:'
echo "$https_headers" | grep -qi 'x-content-type-options: nosniff'
echo "$https_headers" | grep -qi '^server: nginx$'
http_hsts="$(curl -sI "http://127.0.0.1:${HTTP_PORT}/" | grep -i strict-transport || true)"
[[ -z "$http_hsts" ]] || { echo "Unexpected HSTS on HTTP" >&2; exit 1; }
echo "no HSTS on HTTP (expected)"

echo ""
echo "=== 13. Host :9000 not published ==="
fpm_code="$(curl -sS -o /dev/null -w '%{http_code}' --connect-timeout 1 "http://127.0.0.1:9000/" 2>/dev/null)" || fpm_code="000"
echo "host :9000 → http_code=${fpm_code}"
[[ "${fpm_code}" != "200" ]]

echo ""
echo "=== 14. Optional 502 when app stopped ==="
docker compose stop app
sleep 1
stopped_code="$(curl -sS -o /dev/null -w '%{http_code}' "http://127.0.0.1:${HTTP_PORT}/index.php" 2>/dev/null)" || stopped_code="000"
echo "with app stopped → ${stopped_code}"
[[ "${stopped_code}" != "200" ]]
docker compose start app
sleep 2
recovered_code="$(curl -fsS -o /dev/null -w '%{http_code}' "http://127.0.0.1:${HTTP_PORT}/index.php")"
echo "after app restart → ${recovered_code}"
[[ "$recovered_code" == "200" ]]

docker compose down -v

echo ""
echo "Solution demo OK: chapter 11 nginx + PHP-FPM checkpoint."
