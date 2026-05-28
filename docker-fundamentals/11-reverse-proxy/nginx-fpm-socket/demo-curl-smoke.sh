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
  local want="${HTTP_PORT:-8080}"
  if ! port_in_use "$want"; then
    echo "$want"
    return
  fi
  for try in 8081 8082 18080 28080; do
    if ! port_in_use "$try"; then
      echo "Port ${want} busy; using ${try}" >&2
      echo "$try"
      return
    fi
  done
  echo "No free port. Set HTTP_PORT in .env" >&2
  exit 1
}

export HTTP_PORT="$(pick_port)"
BASE="http://127.0.0.1:${HTTP_PORT}"

docker compose down -v 2>/dev/null || true

echo "=== 1. Bring stack up ==="
docker compose up -d
sleep 2
docker compose ps

echo ""
echo "=== 2. HTTP smoke (full response) ==="
headers="$(curl -fsSI "${BASE}/index.php")"
body="$(curl -fsS "${BASE}/index.php")"
echo "$headers" | head -8
echo "---"
echo "$body"
echo "$headers" | grep -qi 'HTTP/1.1 200'
echo "$body" | grep -qi 'FastCGI via'

echo ""
echo "=== 3. Status code only (CI-friendly) ==="
code="$(curl -fsS -o /dev/null -w '%{http_code}' "${BASE}/index.php")"
echo "$code"
[[ "$code" == "200" ]]

echo ""
echo "=== 4. Front controller path / ==="
root_body="$(curl -fsS "${BASE}/")"
echo "$root_body"
echo "$root_body" | grep -qi 'FastCGI via'

echo ""
echo "=== 5. Virtual host with --resolve ==="
vhost_url="http://app.localhost:${HTTP_PORT}/index.php"
vhost_body="$(curl -fsS --resolve "app.localhost:${HTTP_PORT}:127.0.0.1" "$vhost_url")"
echo "$vhost_body"
echo "$vhost_body" | grep -qi 'FastCGI via'

echo ""
echo "=== 6. Host port 9000 is not published (do not curl FPM as HTTP) ==="
fpm_code="000"
fpm_code="$(curl -sS -o /dev/null -w '%{http_code}' --connect-timeout 1 "http://127.0.0.1:9000/" 2>/dev/null)" || true
echo "curl http://127.0.0.1:9000/ → http_code=${fpm_code:-refused}"
[[ "${fpm_code:-000}" != "200" ]]

echo ""
echo "=== 7. CI one-liner ==="
test "$(curl -fsS -o /dev/null -w '%{http_code}' "${BASE}/index.php")" = "200"
echo "proxy smoke exit=0"

echo ""
echo "=== 8. Headers only (-I) ==="
curl -fsS -I "${BASE}/index.php" | grep -qi 'content-type: text/plain'

docker compose down -v

echo ""
echo "Demo OK: curl smoke through nginx → PHP-FPM (lesson 11.7)."
echo "HTTPS smoke: see ../tls-edge-plain-fastcgi/demo.sh (lesson 11.5)."
