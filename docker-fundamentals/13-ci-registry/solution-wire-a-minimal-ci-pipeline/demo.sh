#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

REG_PORT="${REG_PORT:-5001}"
REG="127.0.0.1:${REG_PORT}"
IMAGE="${REG}/ch13-ci-exercise"
SHA_TAG=local-sha-demo
CANDIDATE="ci-candidate:local"

port_in_use() {
  local p="$1"
  if command -v nc >/dev/null 2>&1; then
    nc -z 127.0.0.1 "$p" 2>/dev/null
  else
    (echo >/dev/tcp/127.0.0.1/"${p}") >/dev/null 2>&1
  fi
}

if port_in_use "$REG_PORT"; then
  for try in 5002 5003 15001; do
    if ! port_in_use "$try"; then
      echo "Port ${REG_PORT} busy; using ${try}" >&2
      REG_PORT="$try"
      REG="127.0.0.1:${REG_PORT}"
      IMAGE="${REG}/ch13-ci-exercise"
      break
    fi
  done
fi

workflow=".github/workflows/image.yml"

echo "=== Workflow spine checks (static) ==="
grep -q 'packages: write' "$workflow"
grep -q 'pull-requests: write' "$workflow"
grep -q 'load: true' "$workflow"
grep -q "grep -q 'ch13 ci exercise ok'" "$workflow"
grep -q 'aquasecurity/trivy-action' "$workflow"
grep -q 'exit-code: 1' "$workflow"
grep -q 'id: build' "$workflow"
grep -q 'steps.build.outputs.digest' "$workflow"
grep -q 'github-script' "$workflow"
grep -qv 'CMD true' "$workflow" || true
echo "OK: workflow includes load → smoke → trivy → push → PR comment"

echo ""
echo "=== 1. Build candidate (load only — no push) ==="
docker build -t "$CANDIDATE" .

echo ""
echo "=== 2. Smoke test ==="
docker run --rm "$CANDIDATE" php smoke.php | grep -q 'ch13 ci exercise ok'
echo "smoke passed"

echo ""
echo "=== 3. Trivy gate (optional local) ==="
if command -v trivy >/dev/null 2>&1; then
  trivy image --severity HIGH,CRITICAL --ignore-unfixed --exit-code 1 "$CANDIDATE"
  echo "trivy passed"
else
  echo "skip: trivy not installed locally (CI runs aquasecurity/trivy-action)"
fi

echo ""
echo "=== 4. Broken smoke — fails before push ==="
docker build -f Dockerfile.broken -t ci-candidate:broken .
if docker run --rm ci-candidate:broken 2>/dev/null; then
  echo "Expected broken image smoke to fail" >&2
  exit 1
fi
echo "broken smoke failed as expected"

echo ""
echo "=== 5. Push after gates (local registry simulates GHCR) ==="
docker rm -f ch13-reg 2>/dev/null || true
docker run -d --name ch13-reg -p "127.0.0.1:${REG_PORT}:5000" registry:2
sleep 2
docker tag "$CANDIDATE" "${IMAGE}:${SHA_TAG}"
docker tag "$CANDIDATE" "${IMAGE}:pr-1"
docker push "${IMAGE}:${SHA_TAG}"
docker push "${IMAGE}:pr-1"
digest="$(docker buildx imagetools inspect "${IMAGE}:${SHA_TAG}" --format '{{json .Manifest.Digest}}' | tr -d '"')"
echo "digest=${digest}"
echo "Deploy pin: ${IMAGE}@${digest}"

echo ""
echo "=== 6. Pull by digest (promote-by-digest) ==="
docker pull "${IMAGE}@${digest}"
docker run --rm "${IMAGE}@${digest}" php smoke.php | grep -q 'ch13 ci exercise ok'

docker rm -f ch13-reg 2>/dev/null || true

echo ""
echo "Solution demo OK: chapter 13 minimal CI pipeline (local rehearsal)."
echo "Push this folder to GitHub and open a PR to run the full workflow on Actions."
