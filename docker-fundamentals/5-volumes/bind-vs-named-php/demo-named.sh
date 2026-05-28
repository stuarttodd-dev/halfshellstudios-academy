#!/usr/bin/env bash
set -euo pipefail

VOLUME=ch5-bind-vs-named-demo

docker volume create "$VOLUME" >/dev/null 2>&1 || true

echo "=== Named volume: write from one container ==="
docker run --rm -v "$VOLUME":/data alpine sh -c 'echo db-row > /data/seed.txt'

echo "=== Named volume: read from a new container ==="
docker run --rm -v "$VOLUME":/data alpine cat /data/seed.txt

echo ""
echo "Mountpoint (Docker-managed storage):"
docker volume inspect "$VOLUME" --format '{{ .Mountpoint }}'

docker volume rm "$VOLUME" >/dev/null
echo ""
echo "Named volume demo OK."
