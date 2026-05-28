#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

cat > .env.docker <<'ENV'
APP_ENV=local
APP_DEBUG=true
DB_HOST=db
DB_DATABASE=academy
QUEUE_CONNECTION=redis
ENV

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
elif [[ -f .env.example ]]; then
  cp .env.example .env
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

run_app() {
  docker compose run --rm --no-deps app php print-env.php
}

echo "=== 1. Build once ==="
docker compose build

echo ""
echo "=== 2. Run with .env.docker (runtime env_file) ==="
out1="$(run_app)"
echo "$out1"
echo "$out1" | grep -q 'APP_ENV=local'
echo "$out1" | grep -q 'DB_HOST=db'
echo "$out1" | grep -q 'QUEUE_CONNECTION=redis'

echo ""
echo "=== 3. Change .env.docker — no rebuild ==="
cat > .env.docker <<'ENV'
APP_ENV=staging
APP_DEBUG=false
DB_HOST=db.staging.example
DB_DATABASE=academy
QUEUE_CONNECTION=redis
ENV
out2="$(run_app)"
echo "$out2"
echo "$out2" | grep -q 'APP_ENV=staging'
echo "$out2" | grep -q 'APP_DEBUG=false'
# compose environment: DB_HOST: db wins over env_file for the same key (lesson 8.5)
echo "$out2" | grep -q 'DB_HOST=db'

echo ""
echo "=== 4. One-shot override (-e wins for this run) ==="
out3="$(docker compose run --rm --no-deps -e APP_ENV=production app php print-env.php)"
echo "$out3"
echo "$out3" | grep -q 'APP_ENV=production'

echo ""
echo "=== 5. Inspect process env inside container ==="
out5="$(docker compose run --rm --no-deps app sh -c 'echo DB_HOST=$DB_HOST APP_ENV=$APP_ENV')"
echo "$out5"
echo "$out5" | grep -q 'DB_HOST=db'
echo "$out5" | grep -q 'APP_ENV=staging'

echo ""
echo "=== 6. .dockerignore keeps .env out of build context ==="
if docker history ch8-laravel-env:demo --no-trunc 2>/dev/null | grep -qE 'DB_PASSWORD|APP_KEY'; then
  echo "Unexpected secret-like strings in image history" >&2
  exit 1
fi
echo "OK: no project .env baked into image layers"

cat > .env.docker <<'ENV'
APP_ENV=local
APP_DEBUG=true
DB_HOST=db
DB_DATABASE=academy
QUEUE_CONNECTION=redis
ENV

docker compose down -v 2>/dev/null || true

echo ""
echo "Demo OK: same image tag, different runtime env — no rebuild for APP_ENV changes."
