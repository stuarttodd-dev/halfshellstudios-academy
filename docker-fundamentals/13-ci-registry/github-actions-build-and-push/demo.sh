#!/usr/bin/env bash
set -euo pipefail

# Local rehearsal of build-push-action with push: true (lesson 13.3 try-it §1)
cd "$(dirname "$0")"

REG_PORT="${REG_PORT:-5001}"
REG="127.0.0.1:${REG_PORT}"
IMAGE="${REG}/ch13-gha-demo"
TAG=local

echo "=== Start local registry on ${REG} ==="
docker rm -f ch13-reg 2>/dev/null || true
docker run -d --name ch13-reg -p "127.0.0.1:${REG_PORT}:5000" registry:2
sleep 2

echo ""
echo "=== buildx build --push (mirrors build-push-action) ==="
docker buildx build \
  --tag "${IMAGE}:${TAG}" \
  --push \
  --provenance=false \
  --sbom=false \
  .

echo ""
echo "=== Read manifest digest (what CI exports as steps.build.outputs.digest) ==="
digest="$(docker buildx imagetools inspect "${IMAGE}:${TAG}" --format '{{json .Manifest.Digest}}' | tr -d '"')"
echo "digest=${digest}"
[[ "$digest" == sha256:* ]] || { echo "Expected sha256 digest" >&2; exit 1; }

echo ""
echo "=== Pull by digest ==="
docker pull "${IMAGE}@${digest}"

docker rm -f ch13-reg 2>/dev/null || true

echo ""
echo "Demo OK: pushed to local registry; digest captured (same spine as GH Actions + GHCR)."
