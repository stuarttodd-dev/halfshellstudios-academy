#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

docker compose -f compose.minimal.yaml down 2>/dev/null || true
docker rm -f ch12-app ch12-fail 2>/dev/null || true
echo "Removed ch12 demo containers (if present)."
