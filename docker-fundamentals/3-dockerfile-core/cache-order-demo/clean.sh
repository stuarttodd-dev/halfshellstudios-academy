#!/usr/bin/env bash
set -euo pipefail

docker rmi cache-order:bad cache-order:good 2>/dev/null || true
echo "Removed cache-order:bad and cache-order:good."
