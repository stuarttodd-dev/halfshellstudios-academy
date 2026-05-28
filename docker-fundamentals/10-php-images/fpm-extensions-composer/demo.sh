#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

TAG=ch10-pdo:0.1
BASE=php:8.3-fpm-alpine

echo "=== Build Alpine FPM + pdo_mysql ==="
docker build -t "$TAG" .

echo ""
echo "=== PHP version ==="
docker run --rm "$TAG" php -v | head -1

echo ""
echo "=== PDO modules ==="
docker run --rm "$TAG" php -m | grep -E '^PDO$|^pdo_mysql$'

echo ""
echo "=== extension_loaded('pdo_mysql') ==="
docker run --rm "$TAG" php -r "var_dump(extension_loaded('pdo_mysql'));"

echo ""
echo "=== FPM ==="
docker run --rm "$TAG" php-fpm -v | head -1
docker run --rm "$TAG" php-fpm -t 2>&1 | grep -qi 'successful'

echo ""
echo "=== Compare to vanilla official image ==="
if docker run --rm "$BASE" php -m 2>/dev/null | grep -qi pdo_mysql; then
  echo "Unexpected: vanilla $BASE already has pdo_mysql" >&2
  exit 1
fi
echo "OK: vanilla $BASE does not ship pdo_mysql"

echo ""
rt_bytes="$(docker image inspect "$TAG" --format '{{.Size}}')"
base_bytes="$(docker image inspect "$BASE" --format '{{.Size}}' 2>/dev/null || true)"
if [[ -n "$base_bytes" ]]; then
  echo "Image sizes: ${TAG}=${rt_bytes} bytes, ${BASE}=${base_bytes} bytes"
fi

echo ""
echo "Demo OK: official php:8.3-fpm-alpine + pdo_mysql via docker-php-ext-install."
