#!/usr/bin/env bash
set -euo pipefail

docker rmi ch8-config:v1 ch8-config:v2 2>/dev/null || true
echo "Removed ch8-config:v1 and ch8-config:v2."
