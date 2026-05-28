#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

APP=ch12-app
FAIL=ch12-fail

./clean.sh 2>/dev/null || true

echo "=== 1. Start PHP-FPM container (layer 1: must be Up) ==="
docker run -d --name "$APP" php:8.3-fpm-alpine
docker ps --filter "name=^/${APP}$" --format '{{.Names}} {{.Status}}' | grep -qi up

echo ""
echo "=== 2. One-shot exec (no interactive shell) ==="
docker exec "$APP" php -v | head -1
docker exec "$APP" php -m | head -5
docker exec "$APP" printenv HOSTNAME
docker exec "$APP" php-fpm -t 2>&1 | grep -qi successful
docker exec "$APP" sh -c 'ps aux | head -8' | grep -qi php-fpm

echo ""
echo "=== 3. Inspect startup config ==="
inspect_out="$(docker inspect "$APP" --format 'entrypoint={{json .Config.Entrypoint}} cmd={{json .Config.Cmd}}')"
echo "$inspect_out"
echo "$inspect_out" | grep -qi php-fpm

echo ""
echo "=== 4. Working directory flag ==="
docker exec -w /var/www/html "$APP" ls -la | head -3

echo ""
echo "=== 5. Exec fails when container stopped ==="
docker stop "$APP" >/dev/null
exec_err="$(docker exec "$APP" php -v 2>&1)" || true
echo "$exec_err"
echo "$exec_err" | grep -qi 'not running'
docker start "$APP" >/dev/null
docker exec "$APP" php -v | head -1
echo "OK: exec refused while stopped; works after docker start"

echo ""
echo "=== 6. Crash loop — exec cannot help ==="
docker run -d --name "$FAIL" alpine false 2>/dev/null || true
sleep 1
docker ps -a --filter "name=^/${FAIL}$" --format '{{.Status}}' | grep -qi exited
fail_err="$(docker exec "$FAIL" sh 2>&1)" || true
echo "$fail_err"
echo "$fail_err" | grep -qi 'not running'

echo ""
echo "=== 7. Compose exec habit ==="
docker compose -f compose.minimal.yaml up -d
docker compose -f compose.minimal.yaml exec -T app php -v | head -1
db_host="$(docker compose -f compose.minimal.yaml exec -T app printenv DB_HOST)"
echo "DB_HOST=${db_host}"
[[ "$db_host" == "db" ]]
docker compose -f compose.minimal.yaml logs app --tail 3
docker compose -f compose.minimal.yaml down

./clean.sh

echo ""
echo "Demo OK: layer 3 exec — one-shot checks, inspect Cmd, stopped vs crash loop."
