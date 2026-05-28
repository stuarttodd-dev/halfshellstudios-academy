#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose -f compose.yaml -f compose.dev.yaml down 2>/dev/null || true
docker compose -f compose.yaml -f compose.prod.yaml down 2>/dev/null || true
echo "Stopped ch15-bind stack."
