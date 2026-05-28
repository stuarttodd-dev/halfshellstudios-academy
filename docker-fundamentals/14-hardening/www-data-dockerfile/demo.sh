#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
TAG=ch14-www-data

echo "=== 1. Baseline: stock FPM image runs as root by default ==="
docker run --rm php:8.3-fpm-bookworm id

echo ""
echo "=== 2. Build without chown (broken on purpose) ==="
docker build -f Dockerfile.broken -t "${TAG}:broken" .
broken_out="$(docker run --rm "${TAG}:broken" 2>&1)" || true
echo "$broken_out"
if echo "$broken_out" | grep -qi 'permission denied'; then
  echo "OK: www-data cannot write root-owned storage/"
else
  echo "Expected permission denied on storage/" >&2
  exit 1
fi

echo ""
echo "=== 3. Fix ownership — rebuild with chown ==="
docker build -f Dockerfile -t "$TAG" .
docker image inspect "$TAG" --format 'Config.User={{.Config.User}}'
docker run --rm --entrypoint id "$TAG"
docker run --rm "$TAG"

echo ""
echo "=== 4. Running container — inspect and exec (lesson verify) ==="
docker rm -f ch14-app 2>/dev/null || true
# sleep keeps the container Up so exec works (write-test.sh as CMD exits immediately)
docker run -d --name ch14-app --entrypoint sleep "$TAG" infinity
docker inspect ch14-app --format 'Config.User={{.Config.User}}'
docker exec ch14-app id
docker exec ch14-app ./write-test.sh
docker rm -f ch14-app

echo ""
echo "=== 5. Compose-style override back to root (regression) ==="
docker run --rm --user 0:0 --entrypoint id "$TAG"

echo ""
echo "Demo OK: USER www-data + chown on writable paths."
