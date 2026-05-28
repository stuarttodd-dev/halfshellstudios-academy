#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

TAG_DEPS=ch9-exercise:deps
TAG_RELEASE=ch9-exercise:release
TAG_SINGLE=ch9-exercise:single

if [[ ! -f composer.lock ]]; then
  docker run --rm -v "$PWD:/app" -w /app composer:lts \
    composer update --no-dev --no-interaction
fi

echo "=== Step 1: Build deps stage ==="
docker build --target deps -t "$TAG_DEPS" .
docker run --rm "$TAG_DEPS" test -d /app/vendor
echo "deps vendor OK"

echo ""
echo "=== Step 3: Build runtime release ==="
docker build --target runtime -t "$TAG_RELEASE" .
deps_bytes="$(docker image inspect "$TAG_DEPS" --format '{{.Size}}')"
release_bytes="$(docker image inspect "$TAG_RELEASE" --format '{{.Size}}')"
echo "bytes: deps=${deps_bytes} release=${release_bytes}"
echo "Note: release may exceed deps size (FPM + extensions vs composer:lts only)"

echo ""
echo "=== Step 4: Application smoke test ==="
out="$(docker run --rm "$TAG_RELEASE" php public/index.php 2>&1)"
echo "$out"
echo "$out" | grep -q 'pdo_mysql ok'
echo "$out" | grep -q 'monolog ok'

echo ""
echo "=== Step 5: Extensions in release ==="
docker run --rm "$TAG_RELEASE" php -m | grep -qx pdo_mysql
docker run --rm "$TAG_RELEASE" php -r "exit(extension_loaded('pdo_mysql') ? 0 : 1);"
echo "pdo_mysql check exit=0"

echo ""
echo "=== Step 6: No Composer in runtime ==="
docker run --rm "$TAG_RELEASE" sh -c 'command -v composer || echo "no composer in runtime"' | grep -q 'no composer in runtime'

echo ""
echo "=== Step 7: FPM config ==="
docker run --rm "$TAG_RELEASE" php-fpm -t 2>&1 | grep -qi 'successful'

echo ""
echo "=== Step 8 (optional): single-stage compare ==="
docker build -f Dockerfile.single -t "$TAG_SINGLE" . -q
single_bytes="$(docker image inspect "$TAG_SINGLE" --format '{{.Size}}')"
echo "bytes: single=${single_bytes} release=${release_bytes}"
[[ "$release_bytes" -lt "$single_bytes" ]] || echo "Note: single-stage not smaller on this platform (still valid compare)"

docker rmi "$TAG_DEPS" "$TAG_RELEASE" "$TAG_SINGLE" 2>/dev/null || true

echo ""
echo "Solution demo OK: deps → runtime; vendor bridged; pdo_mysql; no composer in release."
