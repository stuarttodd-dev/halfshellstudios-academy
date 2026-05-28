#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker rm -f ch13-reg 2>/dev/null || true
echo "Removed ch13 CI exercise local registry."
