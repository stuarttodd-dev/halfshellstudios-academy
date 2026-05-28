#!/usr/bin/env bash
set -euo pipefail

docker rmi ch10-user:local ch10-user:broken 2>/dev/null || true
echo "Removed ch10-user demo images (if present)."
