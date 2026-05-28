#!/usr/bin/env bash
set -euo pipefail

NAME=cli-loop
IMAGE=nginx:stable-alpine

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

port_in_use() {
  local p="$1"
  if command -v lsof >/dev/null 2>&1; then
    lsof -nP -iTCP:"${p}" -sTCP:LISTEN >/dev/null 2>&1
  else
    (echo >/dev/tcp/127.0.0.1/"${p}") >/dev/null 2>&1
  fi
}

pick_port() {
  local want="${HTTP_PORT:-8080}"
  if ! port_in_use "$want"; then
    echo "$want"
    return
  fi
  for try in 8081 8082 18080 28080; do
    if ! port_in_use "$try"; then
      echo "Port ${want} busy; using ${try}" >&2
      echo "$try"
      return
    fi
  done
  echo "No free port found (tried ${want} and fallbacks). Set HTTP_PORT in .env" >&2
  exit 1
}

PORT="$(pick_port)"
URL="http://127.0.0.1:${PORT}/"

./clean.sh 2>/dev/null || true

echo "=== Pull (if needed) ==="
docker pull "$IMAGE"

echo ""
echo "=== 1. Run detached with stable name (127.0.0.1:${PORT}→80) ==="
docker run -d --name "$NAME" -p "127.0.0.1:${PORT}:80" "$IMAGE"

echo ""
echo "=== 2. Confirm running ==="
docker ps --filter "name=^/${NAME}$"

echo ""
echo "=== 3. Smoke-test HTTP ==="
curl -fsSI "$URL" | head -1 | grep -q 'HTTP/.* 200'

echo ""
echo "=== 4. Recent logs ==="
docker logs --tail 10 "$NAME"

echo ""
echo "=== 5. Inspect status ==="
status="$(docker inspect --format '{{.State.Status}} restarts={{.RestartCount}}' "$NAME")"
echo "$status"
echo "$status" | grep -q '^running '

echo ""
echo "=== 6. Stop — should show exited ==="
docker stop "$NAME"
docker ps -a --filter "name=^/${NAME}$" --format '{{.Names}} {{.Status}}' | grep -qi exited

echo ""
echo "=== 7. Remove — name free ==="
docker rm "$NAME"
if docker ps -a --filter "name=^/${NAME}$" --format '{{.Names}}' | grep -q .; then
  echo "Container $NAME still listed after rm" >&2
  exit 1
fi

echo ""
echo "Demo OK: full CLI inner loop (run → curl → logs → inspect → stop → rm)."
