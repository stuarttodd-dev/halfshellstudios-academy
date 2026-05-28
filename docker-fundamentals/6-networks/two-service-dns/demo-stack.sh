#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE=(docker compose -f compose.php-mysql-redis.yaml)
PORT="${APP_PORT:-8000}"
URL="http://127.0.0.1:${PORT}/"

echo "=== HTTP via published app port only ==="
body="$(curl -fsS "$URL")"
echo "$body"
echo "$body" | grep -q 'DB_HOST=db'
echo "$body" | grep -q 'db:3306 reachable'
echo "$body" | grep -q 'redis:6379 reachable'

echo ""
echo "=== Resolve service names inside app container ==="
"${COMPOSE[@]}" exec -T app getent hosts db
"${COMPOSE[@]}" exec -T app getent hosts redis

echo ""
echo "=== db and redis are not published to the host ==="
if docker compose -f compose.php-mysql-redis.yaml ps --format json | grep -q '"Publishers":\[\]'; then
  echo "OK: internal services have no host port mapping in compose ps"
fi
"${COMPOSE[@]}" ps

echo ""
echo "Demo OK: app reaches db and redis by service name on the project network."
