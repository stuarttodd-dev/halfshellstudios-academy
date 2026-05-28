#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

docker rm -f ch10-extend-test 2>/dev/null || true
docker rmi ch10-extend:release ch10-extend:deps 2>/dev/null || true
echo "Removed ch10-extend images and test container."
