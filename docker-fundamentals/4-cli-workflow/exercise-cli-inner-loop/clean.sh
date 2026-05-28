#!/usr/bin/env bash
set -euo pipefail

docker rm -f cli-loop 2>/dev/null || true
echo "Removed cli-loop (if it existed)."
