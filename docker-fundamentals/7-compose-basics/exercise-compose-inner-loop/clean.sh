#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose down -v 2>/dev/null || true
echo "Stopped ch7compose-loop (if it was running)."
