#!/usr/bin/env bash
set -euo pipefail

docker rmi ch9-exercise:deps ch9-exercise:release ch9-exercise:single 2>/dev/null || true
echo "Removed ch9-exercise demo images (if present)."
