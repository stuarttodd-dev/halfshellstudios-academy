#!/usr/bin/env bash
set -euo pipefail

docker rmi ch9-named:runtime ch9-named:runtime-explicit ch9-named:build-only ch9-named:numeric 2>/dev/null || true
echo "Removed ch9-named demo images (if present)."
