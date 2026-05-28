#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"
docker compose down -v "$@"
# Restore override if demo left it renamed
if [[ -f compose.override.yaml.bak && ! -f compose.override.yaml ]]; then
  mv compose.override.yaml.bak compose.override.yaml
fi
echo "Stack ch7stack stopped."
