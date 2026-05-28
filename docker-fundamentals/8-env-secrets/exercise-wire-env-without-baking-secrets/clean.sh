#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose down -v 2>/dev/null || true
docker rmi ch8-exercise-env:demo 2>/dev/null || true
echo "Removed ch8-exercise-env stack and image."
