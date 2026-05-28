#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose -f compose.refused-demo.yaml down -v "$@" 2>/dev/null || true
docker compose -f compose.refused-demo-healthy.yaml down -v "$@" 2>/dev/null || true
echo "Stack ch6-refused-demo stopped."
