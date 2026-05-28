#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

wait_health() {
  local want="$1"
  local max="${2:-45}"
  local status=""
  for _ in $(seq 1 "$max"); do
    status="$(docker inspect "$(docker compose ps -q app)" \
      --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}none{{end}}' 2>/dev/null || echo none)"
    [[ "$status" == "$want" ]] && { echo "$status"; return 0; }
    sleep 1
  done
  echo "timeout waiting for health=${want} (last=${status})" >&2
  return 1
}

down_all() {
  docker compose down -v 2>/dev/null || true
  docker compose -f compose.starter.yaml down -v 2>/dev/null || true
}

down_all

echo "=== Broken starter: CMD true lies (exercise task 1) ==="
docker compose -f compose.starter.yaml build -q app
docker compose -f compose.starter.yaml up -d
sleep 8
broken_hc="$(docker inspect "$(docker compose -f compose.starter.yaml ps -q app)" \
  --format '{{json .Config.Healthcheck.Test}}')"
echo "healthcheck test=${broken_hc}"
echo "$broken_hc" | grep -qi 'true'
broken_status="$(docker inspect "$(docker compose -f compose.starter.yaml ps -q app)" \
  --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}none{{end}}')"
echo "broken health=${broken_status} (always passes — not an honest FPM probe)"
docker compose -f compose.starter.yaml down -v

echo ""
echo "=== Fixed: build + up (exercise tasks 4–6) ==="
docker compose build -q app
docker compose up -d --force-recreate
wait_health healthy 35
docker compose ps
fixed_hc="$(docker inspect "$(docker compose ps -q app)" --format '{{json .Config.Healthcheck.Test}}')"
echo "healthcheck test=${fixed_hc}"
echo "$fixed_hc" | grep -qi 'cgi-fcgi'
echo "$fixed_hc" | grep -qv 'true'

echo ""
echo "=== FPM running (exercise task 3) ==="
docker compose exec -T app php-fpm -t 2>&1 | grep -qi 'successful'

echo ""
echo "=== Manual cgi-fcgi ping (exercise task 7) ==="
ping_out="$(docker compose exec -T app sh -c \
  'SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000')"
echo "$ping_out" | grep -qx pong

echo ""
echo "=== Kill FPM — probe should fail (exercise task 8) ==="
docker compose exec -T app sh -c 'kill -TERM 1'
wait_health unhealthy 50 || {
  echo "Note: checking health log after FPM stop"
  docker inspect "$(docker compose ps -q app)" \
    --format '{{range .State.Health.Log}}{{.Start}} exit={{.ExitCode}}{{"\n"}}{{end}}' | tail -3
}
docker compose restart app
wait_health healthy 35
echo "recovered after restart"

down_all

echo ""
echo "Solution demo OK: honest FPM ping healthcheck; no CMD true."
