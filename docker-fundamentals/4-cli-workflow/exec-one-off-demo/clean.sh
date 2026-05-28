#!/usr/bin/env bash
set -euo pipefail

docker rm -f ch4-web 2>/dev/null || true
echo "Removed ch4-web (if it existed)."
