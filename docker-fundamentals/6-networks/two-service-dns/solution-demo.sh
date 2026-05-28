#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

echo "=== Solution A: Compose — client → backend by service name ==="
docker compose -f compose.two-services.yaml down -v 2>/dev/null || true
out="$(docker compose -f compose.two-services.yaml run --rm client 2>&1)"
echo "$out"
echo "$out" | grep -qi nginx
echo "$out" | grep -q 'OK: client reached backend'
docker compose -f compose.two-services.yaml down -v 2>/dev/null || true

echo ""
echo "=== Solution B: docker run — alpine → web on demo-net (lesson 6.3) ==="
./down.sh 2>/dev/null || true
./up.sh
./demo.sh
./down.sh

if [[ "${RUN_PHP_STACK:-0}" == 1 ]]; then
  echo ""
  echo "=== Solution C (stretch): app → db + redis by name ==="
  docker compose -f compose.php-mysql-redis.yaml down -v 2>/dev/null || true
  ./up-stack.sh
  ./demo-stack.sh
  ./down-stack.sh
fi

echo ""
echo "Solution demo OK: two services talk over DNS (no hard-coded IPs)."
