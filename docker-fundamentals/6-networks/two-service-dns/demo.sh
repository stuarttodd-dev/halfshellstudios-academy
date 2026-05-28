#!/usr/bin/env bash
set -euo pipefail

NET=demo-net
NAME=web

if ! docker ps --format '{{.Names}}' | grep -qx "$NAME"; then
  echo "Run ./up.sh first." >&2
  exit 1
fi

echo "=== Container-to-container by name (no host port) ==="
html="$(docker run --rm --network "$NET" alpine sh -c 'wget -qO- http://web')"
echo "$html" | head -5
echo "$html" | grep -qi nginx || echo "$html" | grep -qi 'Welcome to nginx'

echo ""
echo "=== DNS lookup on the same network ==="
docker run --rm --network "$NET" alpine getent hosts "$NAME"

echo ""
echo "=== Network membership ==="
docker network inspect "$NET" --format '{{range .Containers}}{{.Name}} {{end}}'

echo ""
echo "Demo OK: alpine reached http://$NAME/ by name on $NET."
