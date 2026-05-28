#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

COMPOSE=(docker compose -f compose.refused-demo.yaml)
COMPOSE_HEALTHY=(docker compose -f compose.refused-demo-healthy.yaml)

down_all() {
  "${COMPOSE[@]}" down -v 2>/dev/null || true
  "${COMPOSE_HEALTHY[@]}" down -v 2>/dev/null || true
}

wait_db_open() {
  local max="${1:-90}"
  for _ in $(seq 1 "$max"); do
    if "${COMPOSE[@]}" exec -T probe nc -zv db 3306 2>&1 | grep -qiE 'open|succeeded'; then
      return 0
    fi
    sleep 2
  done
  echo "MySQL did not accept connections in time." >&2
  "${COMPOSE[@]}" logs db | tail -15 >&2
  return 1
}

down_all

echo "=== 1. Up with service_started only (not service_healthy) ==="
"${COMPOSE[@]}" up -d

resolve_db() {
  if "${COMPOSE[@]}" exec -T probe getent hosts db 2>/dev/null | grep -q db; then
    return 0
  fi
  "${COMPOSE[@]}" exec -T probe nslookup db 2>&1 | grep -q db
}

echo ""
echo "=== 2. DNS works — name resolves ==="
resolve_db

echo ""
echo "=== 3. Early port probe (may refuse while MySQL initializes) ==="
sleep 2
early_out="$("${COMPOSE[@]}" exec -T probe nc -zv db 3306 2>&1)" || true
echo "$early_out"
if echo "$early_out" | grep -qiE 'refused|failed'; then
  echo "OK: saw connection refused / failed — nothing listening yet (timing)"
elif echo "$early_out" | grep -qiE 'open|succeeded'; then
  echo "Note: MySQL was already up on this machine; timing varies by host speed"
fi

echo ""
echo "=== 4. Wait until MySQL listens, probe again ==="
wait_db_open 45
late_out="$("${COMPOSE[@]}" exec -T probe nc -zv db 3306 2>&1)"
echo "$late_out"
echo "$late_out" | grep -qiE 'open|succeeded'

"${COMPOSE[@]}" down

echo ""
echo "=== 5. Fix: depends_on service_healthy + healthcheck ==="
"${COMPOSE_HEALTHY[@]}" up -d
for _ in $(seq 1 60); do
  if "${COMPOSE_HEALTHY[@]}" ps db --format '{{.Health}}' 2>/dev/null | grep -q healthy; then
    break
  fi
  sleep 2
done
healthy_out="$("${COMPOSE_HEALTHY[@]}" exec -T probe nc -zv db 3306 2>&1)"
echo "$healthy_out"
echo "$healthy_out" | grep -qiE 'open|succeeded'
echo "OK: probe starts after db is healthy — fewer race refusals"

down_all

echo ""
echo "Demo OK: refused = reached IP:port, no listener yet. Fix readiness, not DNS."
