#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

CANDIDATE="ci-candidate:local"

echo "=== Chapter 13 exercise starter — local build + smoke ==="
echo "Complete .github/workflows/image.yml in this folder, or compare to ../solution-wire-a-minimal-ci-pipeline/"
echo ""

echo "=== 1. Build candidate ==="
docker build -t "$CANDIDATE" .

echo ""
echo "=== 2. Smoke test ==="
docker run --rm "$CANDIDATE" php smoke.php | grep -q 'ch13 ci exercise ok'
echo "smoke passed"

echo ""
echo "=== 3. Broken smoke (optional gate demo) ==="
docker build -f Dockerfile.broken -t ci-candidate:broken .
if docker run --rm ci-candidate:broken 2>/dev/null; then
  echo "Expected broken image smoke to fail" >&2
  exit 1
fi
echo "broken smoke failed as expected"

echo ""
echo "Exercise starter OK. Finish the workflow tasks, then run demo.sh in the solution folder for full spine checks."
