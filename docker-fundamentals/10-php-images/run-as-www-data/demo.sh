#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

TAG=ch10-user:local
TAG_BROKEN=ch10-user:broken

echo "=== Stock image baseline ==="
docker run --rm php:8.3-fpm-bookworm id

echo ""
echo "=== 1. Build good image and check identity ==="
docker build -t "$TAG" .
docker image inspect "$TAG" --format 'Config.User={{.Config.User}}'
id_out="$(docker run --rm --entrypoint id "$TAG")"
echo "$id_out"
echo "$id_out" | grep -q 'uid=33(www-data)'

echo ""
echo "=== 2. Broken build (no chown) — permission denied ==="
docker build -f Dockerfile.broken -t "$TAG_BROKEN" .
broken_out="$(docker run --rm "$TAG_BROKEN" php -r "file_put_contents('storage/logs/x.log','t');" 2>&1)" || true
echo "$broken_out"
echo "$broken_out" | grep -qi 'permission denied'

echo ""
echo "=== 3. Write through PHP as www-data ==="
out="$(docker run --rm "$TAG" sh -c 'php public/index.php && cat storage/logs/ch10-write-test.log')"
echo "$out"
echo "$out" | grep -q '^ok$'
echo "$out" | grep -q 'www-data'

echo ""
echo "=== 4. FPM config valid ==="
docker run --rm "$TAG" php-fpm -t 2>&1 | grep -qi 'successful'

echo ""
echo "=== 5. Non-root CI smoke ==="
docker run --rm "$TAG" sh -c 'test "$(id -u)" -eq 33' && echo "non-root OK"

docker rmi "$TAG" "$TAG_BROKEN" 2>/dev/null || true

echo ""
echo "Demo OK: chown storage, USER www-data, verify id + write + php-fpm -t."
