#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

CONTAINER="${MYSQL_CONTAINER:-my_mysql}"
VOLUME="${MYSQL_VOLUME:-mysql_data}"
IMAGE="${MYSQL_IMAGE:-mysql:8.0}"
ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-root}"
DATABASE="${MYSQL_DATABASE:-exercise_db}"
ROW_BODY="${MYSQL_ROW_BODY:-persist me}"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

run_mysql() {
  docker run -d \
    --name "$CONTAINER" \
    -e "MYSQL_ROOT_PASSWORD=${ROOT_PASSWORD}" \
    -v "${VOLUME}:/var/lib/mysql" \
    "$IMAGE"
}

wait_mysql() {
  echo "Waiting for MySQL..."
  for _ in $(seq 1 60); do
    if docker exec "$CONTAINER" mysqladmin ping -h 127.0.0.1 -uroot -p"$ROOT_PASSWORD" --silent 2>/dev/null; then
      return 0
    fi
    sleep 2
  done
  echo "MySQL did not become ready in time." >&2
  docker logs "$CONTAINER" 2>&1 | tail -20 >&2 || true
  exit 1
}

seed_notes() {
  docker exec "$CONTAINER" mysql -uroot -p"$ROOT_PASSWORD" -e \
    "CREATE DATABASE IF NOT EXISTS ${DATABASE}; \
     USE ${DATABASE}; \
     CREATE TABLE IF NOT EXISTS notes (id INT AUTO_INCREMENT PRIMARY KEY, body TEXT); \
     INSERT INTO notes (body) VALUES ('${ROW_BODY}'); \
     SELECT * FROM notes;"
}

assert_row() {
  local out
  out="$(docker exec "$CONTAINER" mysql -uroot -p"$ROOT_PASSWORD" "$DATABASE" -N -e "SELECT body FROM notes LIMIT 1;" 2>/dev/null)"
  echo "$out"
  echo "$out" | grep -qF "$ROW_BODY"
}

./clean.sh 2>/dev/null || true

echo "=== 1. Create named volume ==="
docker volume create "$VOLUME"
docker volume ls | grep -F "$VOLUME"

echo ""
echo "=== 2. Run MySQL with volume mounted ==="
run_mysql
docker ps --filter "name=^/${CONTAINER}$"
wait_mysql

echo ""
echo "=== 3. Seed row (must survive container replace) ==="
seed_notes

echo ""
echo "=== 4. Stop and remove container (volume kept) ==="
docker stop "$CONTAINER"
docker rm "$CONTAINER"
docker ps -a --filter "name=^/${CONTAINER}$" --format '{{.Names}}' | grep -q . && exit 1 || true

echo ""
echo "=== 5. New container on same volume ==="
run_mysql
wait_mysql

echo ""
echo "=== 6. Prove row still exists ==="
assert_row

echo ""
echo "Demo OK: named volume ${VOLUME} outlived container ${CONTAINER}."
echo "Run ./clean.sh when finished (removes container and volume)."
