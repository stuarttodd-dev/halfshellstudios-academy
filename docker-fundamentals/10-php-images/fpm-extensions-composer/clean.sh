#!/usr/bin/env bash
set -euo pipefail

docker rmi ch10-pdo:0.1 ch10-exts:bookworm app-php:exts 2>/dev/null || true
echo "Removed ch10 demo images (if present)."
