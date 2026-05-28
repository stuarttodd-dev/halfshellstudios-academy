#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE_FILE=compose.ports-demo.yaml
COMPOSE=(docker compose -f "$COMPOSE_FILE")

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
  echo "No free port found. Set HTTP_PORT in .env" >&2
  exit 1
}

export HTTP_PORT="$(pick_port)"
BASE_URL="http://127.0.0.1:${HTTP_PORT}/"

"${COMPOSE[@]}" down 2>/dev/null || true

echo "=== Up (edge published on 127.0.0.1:${HTTP_PORT}) ==="
"${COMPOSE[@]}" up -d

echo ""
echo "=== 1. Published edge reachable from host ==="
for _ in $(seq 1 20); do
  if curl -fsS -o /dev/null -w "%{http_code}" "$BASE_URL" 2>/dev/null | grep -q 200; then
    break
  fi
  sleep 1
done
code="$(curl -fsS -o /dev/null -w "%{http_code}" "$BASE_URL")"
echo "HTTP ${code}"
[[ "$code" == "200" ]]

published="$("${COMPOSE[@]}" port edge 80)"
echo "docker compose port edge 80 → ${published}"
echo "$published" | grep -q "127.0.0.1:${HTTP_PORT}"

echo ""
echo "=== 2. Internal DNS and Redis from probe ==="
resolve_ok=false
if "${COMPOSE[@]}" exec -T probe getent hosts cache 2>/dev/null | grep -q cache; then
  resolve_ok=true
elif "${COMPOSE[@]}" exec -T probe nslookup cache 2>&1 | grep -q cache; then
  resolve_ok=true
fi
$resolve_ok || { echo "probe could not resolve cache" >&2; exit 1; }

for _ in $(seq 1 15); do
  if "${COMPOSE[@]}" exec -T probe nc -zv cache 6379 2>&1 | grep -qiE 'open|succeeded'; then
    break
  fi
  sleep 1
done
"${COMPOSE[@]}" exec -T probe nc -zv cache 6379 2>&1 | grep -qiE 'open|succeeded'

echo ""
echo "=== 3. cache not published to host ==="
cache_ports="$("${COMPOSE[@]}" ps cache --format '{{.Ports}}')"
echo "cache Ports column: ${cache_ports}"
if [[ "$cache_ports" == *"->"* ]]; then
  echo "Unexpected: cache is published to the host" >&2
  exit 1
fi
echo "OK: no host mapping (internal 6379/tcp only)"
if nc -zv 127.0.0.1 6379 2>&1 | grep -qiE 'succeeded|open'; then
  echo "Note: something on the host is listening on 6379 (not this stack). Compose map is still correct."
fi

echo ""
echo "=== 4. edge reachable by service name inside the network ==="
edge_html="$("${COMPOSE[@]}" exec -T probe wget -qO- http://edge 2>/dev/null | head -5)"
echo "$edge_html" | head -3
echo "$edge_html" | grep -qiE 'nginx|Welcome'

echo ""
echo "Demo OK: ports: for host; service names for container-to-container."
echo "Run ./down.sh to stop."
