#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

TAG_BLOATED=ch9-php:bloated
TAG_SLIM=ch9-php:slim

if [[ ! -f composer.lock ]]; then
  echo "Generating composer.lock..."
  docker run --rm -v "$PWD:/app" -w /app composer:lts \
    composer install --no-dev --no-interaction
fi

echo "=== Build single-stage (bloated) image ==="
docker build -f Dockerfile.bloated -t "$TAG_BLOATED" .

echo ""
echo "=== Build multi-stage (slim) image ==="
docker build -f Dockerfile.slim -t "$TAG_SLIM" .

bloated_bytes="$(docker image inspect "$TAG_BLOATED" --format '{{.Size}}')"
slim_bytes="$(docker image inspect "$TAG_SLIM" --format '{{.Size}}')"

echo ""
echo "=== Image sizes ==="
docker image ls --format 'table {{.Repository}}:{{.Tag}}\t{{.Size}}' | grep -E 'ch9-php:(bloated|slim)|REPOSITORY' || true
docker image inspect "$TAG_BLOATED" --format 'bloated: {{.Size}} bytes'
docker image inspect "$TAG_SLIM" --format 'slim:    {{.Size}} bytes'

echo ""
echo "bytes: bloated=${bloated_bytes} slim=${slim_bytes}"
if [[ "$slim_bytes" -ge "$bloated_bytes" ]]; then
  echo "Expected slim image smaller than bloated" >&2
  exit 1
fi
saved=$((bloated_bytes - slim_bytes))
pct=$((saved * 100 / bloated_bytes))
echo "OK: slim is ${saved} bytes smaller (~${pct}%)"

echo ""
echo "=== Runtime check (slim) ==="
docker run --rm "$TAG_SLIM" php -r "require 'vendor/autoload.php'; echo 'autoload ok'.PHP_EOL;"
docker run --rm "$TAG_SLIM" sh -c 'command -v composer >/dev/null 2>&1 && exit 1 || echo "OK: no composer in runtime"'
docker run --rm "$TAG_SLIM" sh -c 'command -v git >/dev/null 2>&1 && exit 1 || echo "OK: no git in runtime"'

echo ""
echo "=== History hint (bloated keeps apt/composer layers) ==="
docker history "$TAG_BLOATED" --format '{{.Size}}\t{{.CreatedBy}}' | head -6

echo ""
echo "Demo OK: multi-stage runtime excludes Composer/git; image is smaller."
