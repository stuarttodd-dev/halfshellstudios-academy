#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

NAME=ch4-web
PORT="${HTTP_PORT:-8080}"
URL="http://127.0.0.1:${PORT}/"

./clean.sh 2>/dev/null || true

echo "=== 1. Named long-lived container ==="
docker run -d --name "$NAME" -p "127.0.0.1:${PORT}:80" nginx:stable-alpine
sleep 1
curl -fsSI "$URL" | head -1

echo ""
echo "=== 2. docker exec (same running container) ==="
docker exec "$NAME" nginx -t
docker exec "$NAME" printenv NGINX_VERSION || docker exec "$NAME" nginx -v
docker exec "$NAME" printenv HOSTNAME

echo ""
echo "=== 3. docker run --rm one-offs (new containers, gone on exit) ==="
docker run --rm alpine sh -c 'echo built for $(uname -m); id'
docker run --rm php:8.3-cli-alpine php -r 'echo PHP_VERSION, "\n";'
docker run --rm -v "$PWD:/app" -w /app php:8.3-cli-alpine php hello.php

echo ""
echo "=== 4. No stopped clutter from --rm runs ==="
alpine_left="$(docker ps -a --filter ancestor=alpine --format '{{.ID}}' | wc -l | tr -d ' ')"
php_left="$(docker ps -a --filter ancestor=php:8.3-cli-alpine --format '{{.ID}}' | wc -l | tr -d ' ')"
echo "stopped alpine containers: $alpine_left (expect 0)"
echo "stopped php:8.3-cli-alpine containers: $php_left (expect 0)"

echo ""
echo "=== 5. exec fails when container is stopped ==="
docker stop "$NAME" >/dev/null
exec_err="$(docker exec "$NAME" nginx -v 2>&1)" || true
if echo "$exec_err" | grep -qi 'not running'; then
  echo "OK: exec refused while $NAME is stopped"
else
  echo "$exec_err" >&2
  echo "Expected exec error while stopped" >&2
  exit 1
fi

docker start "$NAME"
docker exec "$NAME" nginx -t
echo "OK: exec works again after docker start"

echo ""
echo "Demo OK. Run ./clean.sh when finished (or: docker exec -it $NAME sh)."
