#!/usr/bin/env bash
set -euo pipefail

docker rm -f ch14-app 2>/dev/null || true
docker rmi ch14-www-data ch14-www-data:broken 2>/dev/null || true
echo "Removed ch14 demo containers/images."
