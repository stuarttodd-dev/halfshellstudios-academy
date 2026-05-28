#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

TAG_BAD=cache-order:bad
TAG_GOOD=cache-order:good
LOG_BAD=/tmp/cache-order-bad-build.log
LOG_GOOD=/tmp/cache-order-good-build.log

echo "=== First build (both variants) ==="
docker build -f Dockerfile.bad -t "$TAG_BAD" .
docker build -f Dockerfile -t "$TAG_GOOD" .

echo ""
echo "=== Edit application source only ==="
printf '%s\n' '<?php' '' 'echo "cache-order-demo v2\n";' > src/index.php

echo ""
echo "=== Rebuild :bad (composer install should rerun) ==="
docker build --progress=plain -f Dockerfile.bad -t "$TAG_BAD" . 2>&1 | tee "$LOG_BAD" | grep -E 'composer install|CACHED' || true
if grep -q 'RUN composer install' "$LOG_BAD" && ! grep -A1 'RUN composer install' "$LOG_BAD" | grep -q CACHED; then
  echo "OK: :bad reran composer install after a source edit"
else
  echo "Review $LOG_BAD — composer step should not be CACHED on :bad"
fi

echo ""
echo "=== Rebuild :good (composer install should be CACHED) ==="
docker build --progress=plain -f Dockerfile -t "$TAG_GOOD" . 2>&1 | tee "$LOG_GOOD" | grep -E 'composer install|CACHED' || true
if grep -E 'composer install|CACHED' "$LOG_GOOD" | grep -q CACHED; then
  echo "OK: :good kept composer install cached after a source edit"
else
  echo "Review $LOG_GOOD — look for CACHED on the composer RUN step"
fi

echo ""
echo "=== Run :good ==="
docker run --rm "$TAG_GOOD"
