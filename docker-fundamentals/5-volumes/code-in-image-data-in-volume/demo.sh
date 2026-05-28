#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE=(docker compose -f compose.ch5-data.yaml)
ROOT_PW="${MARIADB_ROOT_PASSWORD:-secret}"
DB="${MARIADB_DATABASE:-app}"
IMAGE=ch5-code-in-image

echo "=== 1. Code lives in the image (no bind mount) ==="
docker build -f Dockerfile.ch5-code -t "$IMAGE" .
docker run --rm "$IMAGE"

echo ""
echo "=== 2. Bind mount masks image file (dev-only pattern) ==="
echo "from-host" > message.txt
docker run --rm -v "$PWD/message.txt:/message.txt:ro" "$IMAGE" cat /message.txt
rm -f message.txt

echo ""
echo "=== 3. Data in named volume survives compose down ==="
"${COMPOSE[@]}" up -d
echo "Waiting for MariaDB..."
for _ in $(seq 1 40); do
  if "${COMPOSE[@]}" exec -T db healthcheck.sh --connect --innodb_initialized 2>/dev/null; then
    break
  fi
  sleep 2
done

"${COMPOSE[@]}" exec -T db mariadb -uroot -p"$ROOT_PW" "$DB" -e \
  "CREATE TABLE IF NOT EXISTS markers (
     id INT AUTO_INCREMENT PRIMARY KEY,
     tag VARCHAR(32) NOT NULL
   );
   INSERT INTO markers (tag) VALUES ('in-volume');"

"${COMPOSE[@]}" down

"${COMPOSE[@]}" up -d
for _ in $(seq 1 40); do
  if "${COMPOSE[@]}" exec -T db healthcheck.sh --connect --innodb_initialized 2>/dev/null; then
    break
  fi
  sleep 2
done

echo "After down/up (data should persist in dbdata):"
"${COMPOSE[@]}" exec -T db mariadb -uroot -p"$ROOT_PW" "$DB" -e "SELECT * FROM markers;"

echo ""
echo "=== 4. Volume metadata is separate from image IDs ==="
docker volume ls --filter name=ch5-image-vs-volume
docker volume inspect ch5-image-vs-volume_dbdata --format 'Mountpoint={{ .Mountpoint }}'

echo ""
echo "Demo OK. Run ./clean.sh to remove stack and image."
