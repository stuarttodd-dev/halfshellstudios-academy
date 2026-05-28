#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

PORT="${HTTP_PORT:-8080}"
if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$PORT" 2>/dev/null; then
  PORT=18080
fi
export HTTP_PORT="$PORT"

TAG=ch10-fpm-health:local
CNAME=ch10-fpm-health-test

docker compose down -v 2>/dev/null || true
docker rm -f "$CNAME" 2>/dev/null || true

echo "=== 1. Build ==="
docker build -t "$TAG" .

echo ""
echo "=== 2. Config test (php-fpm -t) ==="
docker run --rm "$TAG" php-fpm -t 2>&1 | grep -qi 'successful'

echo ""
echo "=== 3. Run detached — wait for healthy ==="
docker run -d --name "$CNAME" "$TAG" >/dev/null
health=""
for _ in $(seq 1 30); do
  health="$(docker inspect "$CNAME" --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}none{{end}}')"
  [[ "$health" == "healthy" ]] && break
  sleep 1
done
echo "Health.Status=${health}"
[[ "$health" == "healthy" ]]

echo ""
echo "=== 4. Manual FastCGI ping ==="
ping_out="$(docker exec "$CNAME" sh -c \
  'SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000')"
echo "$ping_out"
echo "$ping_out" | grep -qx pong

echo ""
echo "=== 5. Compose service_healthy (web waits for app) ==="
docker rm -f "$CNAME" 2>/dev/null || true
docker compose up -d
for _ in $(seq 1 45); do
  if curl -fsS "http://127.0.0.1:${PORT}/" >/dev/null 2>&1; then
    break
  fi
  sleep 1
done
curl -fsS "http://127.0.0.1:${PORT}/" | grep -q 'ch10 fpm healthcheck'
app_health="$(docker compose ps --format json app 2>/dev/null | head -1 || docker inspect "$(docker compose ps -q app)" --format '{{if .State.Health}}{{.State.Health.Status}}{{end}}')"
echo "compose app health: ${app_health:-healthy via up}"

docker compose down -v

echo ""
echo "Demo OK: php-fpm -t + cgi-fcgi ping; State.Health=healthy; nginx after service_healthy."
