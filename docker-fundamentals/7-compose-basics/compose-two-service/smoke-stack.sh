#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE=(docker compose -f compose.stack.yaml)
port="${HTTP_PORT:-8080}"
mail_port="${MAILHOG_PORT:-8025}"
url="http://127.0.0.1:${port}/stack.php"

echo "=== Web via nginx ==="
body="$(curl -fsS "$url")"
echo "$body"
echo "$body" | grep -q 'DB_HOST=db'
echo "$body" | grep -q 'REDIS_HOST=redis'

echo ""
echo "=== MySQL (service name db) ==="
"${COMPOSE[@]}" exec -T db mysqladmin ping -h 127.0.0.1 -p"${MYSQL_ROOT_PASSWORD:-root_secret}" --silent
echo "mysqladmin ping OK"

echo ""
echo "=== Redis ==="
"${COMPOSE[@]}" exec -T redis redis-cli ping | grep -q PONG
echo "redis-cli ping OK"

echo ""
if "${COMPOSE[@]}" ps --status running mailhog 2>/dev/null | grep -q mailhog; then
  echo "=== Mailhog UI (profile mail active) ==="
  curl -fsS "http://127.0.0.1:${mail_port}/api/v2/messages" >/dev/null
  echo "Mailhog API reachable on :${mail_port}"
else
  echo "=== Mailhog (profile mail not started — expected for lean up) ==="
  if "${COMPOSE[@]}" ps -a mailhog 2>/dev/null | grep -q mailhog; then
    echo "mailhog container exists but is not the default profile"
  else
    echo "mailhog not running (use ./up-stack-mail.sh to opt in)"
  fi
fi

echo ""
echo "Smoke OK."
