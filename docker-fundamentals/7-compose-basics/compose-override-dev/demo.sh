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
URL="http://127.0.0.1:${HTTP_PORT}/"

down() {
  docker compose down -v "$@" 2>/dev/null || true
}

write_default_index() {
  cat > public/index.php <<'PHP'
<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

echo "ch7 override demo\n";
echo 'APP_DEBUG=' . (getenv('APP_DEBUG') ?: '(unset)') . "\n";
echo 'DB_HOST=' . (getenv('DB_HOST') ?: '(unset)') . "\n";
PHP
}

down
write_default_index

echo "=== 1. Merged config (base + compose.override.yaml) ==="
merged="$(docker compose config)"
echo "$merged" | grep -q 'APP_DEBUG'
echo "$merged" | grep -q 'DB_HOST: db'
echo "$merged" | grep -q '/var/www/html'
echo "OK: override adds APP_DEBUG + bind mount; base keeps DB_HOST"

echo ""
echo "=== 2. Up with override — live edit via bind mount ==="
docker compose up -d --build
sleep 3
body="$(curl -fsS "$URL")"
echo "$body"
echo "$body" | grep -q 'ch7 override demo'
echo "$body" | grep -q 'APP_DEBUG=true'

echo ""
echo "=== 3. Edit on host — no rebuild ==="
cat > public/index.php <<'PHP'
<?php
header('Content-Type: text/plain; charset=utf-8');
echo "edited on host\n";
PHP
sleep 1
docker compose exec -T php cat /var/www/html/public/index.php | grep -q 'edited on host'
echo "OK: bind mount — php container sees host edit (no rebuild)"

write_default_index

echo ""
echo "=== 4. Without override (base only) ==="
down
if [[ -f compose.override.yaml ]]; then
  mv compose.override.yaml compose.override.yaml.bak
fi
base_cfg="$(docker compose config)"
if echo "$base_cfg" | grep -qE 'APP_DEBUG|/var/www/html'; then
  echo "Unexpected override keys in base-only config" >&2
  exit 1
fi
echo "OK: base-only config has no APP_DEBUG / project bind mount on php"
docker compose up -d
mv compose.override.yaml.bak compose.override.yaml 2>/dev/null || true

down

echo ""
echo "Demo OK: compose.override.yaml merges automatically; use docker compose config to verify."
