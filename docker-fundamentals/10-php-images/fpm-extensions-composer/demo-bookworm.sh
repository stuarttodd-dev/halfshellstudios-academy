#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

TAG=ch10-exts:bookworm

echo "=== Build bookworm FPM + pdo_mysql, intl, gd, redis ==="
docker build -f Dockerfile.bookworm -t "$TAG" .

echo ""
echo "=== php -m (lesson verify) ==="
docker run --rm "$TAG" php -m | grep -E 'pdo_mysql|intl|gd|redis'

mods="$(docker run --rm "$TAG" php -m)"
for ext in pdo_mysql intl gd redis; do
  echo "$mods" | grep -qx "$ext" || { echo "Missing extension: $ext" >&2; exit 1; }
done

echo ""
echo "=== FPM config test ==="
docker run --rm "$TAG" php-fpm -t 2>&1 | grep -qi 'successful'

echo ""
echo "Demo OK: lesson 10.4 extension bundle on bookworm."
