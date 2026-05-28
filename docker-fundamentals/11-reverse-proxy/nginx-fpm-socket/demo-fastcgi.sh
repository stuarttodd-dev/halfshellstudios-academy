#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

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
  echo "No free port. Set HTTP_PORT in .env" >&2
  exit 1
}

export HTTP_PORT="$(pick_port)"
URL="http://127.0.0.1:${HTTP_PORT}/index.php"
COMPOSE_TCP=(docker compose -f compose.yaml)
COMPOSE_SOCK=(docker compose -f compose.socket.yaml)

down_all() {
  "${COMPOSE_TCP[@]}" down -v 2>/dev/null || true
  "${COMPOSE_SOCK[@]}" down -v 2>/dev/null || true
}

down_all

echo "=== 1. TCP mode — fastcgi_pass app:9000 ==="
"${COMPOSE_TCP[@]}" up -d
sleep 2
body="$(curl -fsS "$URL")"
echo "$body"
echo "$body" | grep -qi 'FastCGI via tcp'
"${COMPOSE_TCP[@]}" exec -T web sh -c 'nc -zv app 9000 2>&1' | grep -qiE 'open|succeeded'
echo "OK: TCP FastCGI; web reaches app:9000"

echo ""
echo "=== 2. Socket mode — unix:/var/run/php/php-fpm.sock ==="
"${COMPOSE_TCP[@]}" down
"${COMPOSE_SOCK[@]}" up -d
sleep 2
"${COMPOSE_SOCK[@]}" exec -T app ls -l /var/run/php/ | grep -q 'php-fpm.sock'
body="$(curl -fsS "$URL")"
echo "$body"
echo "$body" | grep -qi 'FastCGI via socket'
echo "OK: shared volume socket; PHP responds through nginx"

echo ""
echo "=== 3. Socket mode — FPM not on TCP (pairing check) ==="
if "${COMPOSE_SOCK[@]}" exec -T app sh -c 'nc -zv 127.0.0.1 9000 2>&1' | grep -qiE 'open|succeeded'; then
  echo "Note: FPM may still accept 9000; nginx uses socket via default-socket.conf"
else
  echo "OK: nothing listening on 127.0.0.1:9000 inside app (socket-only)"
fi

down_all

echo ""
echo "Demo OK: TCP and Unix socket modes (matching FPM listen + fastcgi_pass)."
echo "Lesson 11.6: break the pair (socket FPM + fastcgi_pass app:9000) to see 502 — try manually."
