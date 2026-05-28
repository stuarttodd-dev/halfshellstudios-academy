#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

docker compose -f compose.ch5-data.yaml down -v 2>/dev/null || true
docker rmi ch5-code-in-image 2>/dev/null || true
rm -f message.txt
echo "Removed ch5 stack (with volumes) and ch5-code-in-image image."
