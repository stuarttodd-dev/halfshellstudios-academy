#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE=(docker compose -f compose.mysql.yaml)
export MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-secret}"
export MYSQL_DATABASE="${MYSQL_DATABASE:-app}"

wait_mysql() {
  echo "Waiting for MySQL..."
  for _ in $(seq 1 40); do
    if "${COMPOSE[@]}" exec -T db mysqladmin ping -h 127.0.0.1 -p"$MYSQL_ROOT_PASSWORD" --silent 2>/dev/null; then
      return 0
    fi
    sleep 2
  done
  echo "MySQL did not become ready in time." >&2
  "${COMPOSE[@]}" logs db | tail -20 >&2 || true
  exit 1
}

seed_notes() {
  "${COMPOSE[@]}" exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" -e \
    "CREATE TABLE IF NOT EXISTS notes (
       id INT AUTO_INCREMENT PRIMARY KEY,
       body TEXT NOT NULL
     );
     INSERT INTO notes (body) VALUES ('first row');"
}

query_notes() {
  "${COMPOSE[@]}" exec -T db mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" -e "SELECT * FROM notes;"
}

echo "=== Up + seed one row ==="
"${COMPOSE[@]}" up -d
wait_mysql
seed_notes
echo "After seed:"
query_notes

echo ""
echo "=== docker compose down (containers gone, volume kept) ==="
"${COMPOSE[@]}" down

echo ""
echo "=== Up again — row should still exist ==="
"${COMPOSE[@]}" up -d
wait_mysql
echo "After down/up:"
query_notes

echo ""
echo "Demo OK: named volume survived compose down."
