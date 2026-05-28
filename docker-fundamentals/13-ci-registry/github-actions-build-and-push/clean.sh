#!/usr/bin/env bash
set -euo pipefail

docker rm -f ch13-reg 2>/dev/null || true
docker rmi 127.0.0.1:5001/ch13-gha-demo:local 2>/dev/null || true
echo "Removed local registry demo resources (if present)."
