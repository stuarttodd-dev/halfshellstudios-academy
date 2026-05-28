#!/usr/bin/env bash
set -euo pipefail

docker rmi ch9-php:bloated ch9-php:slim 2>/dev/null || true
echo "Removed ch9-php demo images (if present)."
