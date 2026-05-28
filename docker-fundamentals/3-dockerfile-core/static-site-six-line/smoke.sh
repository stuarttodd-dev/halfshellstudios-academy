#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

TAG="${1:-static-exercise:0.1}"
NAME=static-exercise
PORT="${HTTP_PORT:-8080}"
URL="http://127.0.0.1:${PORT}/"

if command -v hadolint >/dev/null 2>&1; then
  echo "=== hadolint ==="
  hadolint --failure-threshold warning Dockerfile
else
  echo "=== hadolint (skipped — install hadolint for lesson lint step) ==="
fi

echo ""
echo "=== docker build ==="
docker build --progress=plain -t "$TAG" .

docker rm -f "$NAME" 2>/dev/null || true
echo ""
echo "=== docker run ==="
docker run --rm -d -p "127.0.0.1:${PORT}:80" --name "$NAME" "$TAG"

sleep 1
echo ""
echo "=== curl ==="
curl -sS "$URL" | grep "Built with my Dockerfile"
curl -sS -o /dev/null -w "HTTP %{http_code}\n" "$URL"

echo ""
echo "=== docker logs (tail) ==="
docker logs --tail 5 "$NAME"

docker stop "$NAME"
echo ""
echo "Smoke OK."
