#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

REG_PORT="${REG_PORT:-5001}"
REG="127.0.0.1:${REG_PORT}"
IMAGE="${REG}/ch13-smoke"
TAG=ci

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
      IMAGE="${REG}/ch13-smoke"
      break
    fi
  done
fi

docker rm -f ch13-reg 2>/dev/null || true

run_smoke() {
  local img="$1"
  docker run --rm "$img" php -v | head -1
  out="$(docker run --rm "$img")"
  echo "$out"
  echo "$out" | grep -qx 'ch13 smoke ok'
}

echo "=== 1. Build (no push yet) ==="
docker build -t ch13-smoke:local .

echo ""
echo "=== 2. Smoke on loaded image ==="
run_smoke ch13-smoke:local
echo "smoke passed, ok to push"

echo ""
echo "=== 3. Broken image — smoke fails before push ==="
docker build -f Dockerfile.broken -t ch13-smoke:broken .
if run_smoke ch13-smoke:broken 2>/dev/null; then
  echo "Expected broken smoke to fail" >&2
  exit 1
else
  echo "broken smoke failed as expected (would not push)"
fi

echo ""
echo "=== 4. Push only after good smoke ==="
docker run -d --name ch13-reg -p "127.0.0.1:${REG_PORT}:5000" registry:2
sleep 2
docker tag ch13-smoke:local "${IMAGE}:${TAG}"
docker push "${IMAGE}:${TAG}"
digest="$(docker buildx imagetools inspect "${IMAGE}:${TAG}" --format '{{json .Manifest.Digest}}' | tr -d '"')"
echo "pushed ${IMAGE}:${TAG} digest=${digest}"

echo ""
echo "=== 5. Pull from registry ==="
docker pull "${IMAGE}@${digest}"
docker run --rm "${IMAGE}@${digest}" | grep -qx 'ch13 smoke ok'

docker rm -f ch13-reg 2>/dev/null || true

echo ""
echo "Demo OK: build → smoke → push; broken image caught before publish."
