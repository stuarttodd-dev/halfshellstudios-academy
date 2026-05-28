#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

setup_env_files() {
  cat > .env <<'ENV'
DB_PASSWORD=not-a-real-db-pass
APP_ENV=local
ENV

  cat > .env.docker <<'ENV'
APP_ENV=local
APP_DEBUG=true
DB_HOST=db
DB_DATABASE=academy
APP_KEY=not-a-real-app-key
ENV
}

setup_env_files

docker compose down -v 2>/dev/null || true

echo "=== 5. Build once ==="
docker compose build -q
IMAGE_ID="$(docker images ch8-exercise-env:demo --format '{{.ID}}' | head -1)"
echo "image ch8-exercise-env:demo ID=${IMAGE_ID}"

echo ""
echo "=== 6. Runtime env (no secrets in Dockerfile) ==="
out1="$(docker compose run --rm --no-deps app php print-env.php)"
echo "$out1"
echo "$out1" | grep -q 'DB_HOST=db'
echo "$out1" | grep -q 'APP_KEY=not-a-real-app-key'

echo ""
echo "=== 7. Secrets not in image artifact ==="
if docker image inspect ch8-exercise-env:demo --format '{{ json .Config.Env }}' | grep -qiE 'APP_KEY|DB_PASSWORD|not-a-real'; then
  echo "FAIL: secret in image Config.Env" >&2
  exit 1
fi
echo "OK: no secret in image ENV"
if docker history --no-trunc ch8-exercise-env:demo 2>/dev/null | grep -qiE 'not-a-real|APP_KEY=|DB_PASSWORD='; then
  echo "FAIL: secret-like strings in image history" >&2
  exit 1
fi
echo "OK: no obvious secret in history"

echo ""
echo "=== 8. Change .env.docker — no rebuild ==="
cat > .env.docker <<'ENV'
APP_ENV=staging
APP_DEBUG=true
DB_HOST=db
DB_DATABASE=academy
APP_KEY=not-a-real-app-key
ENV
out2="$(docker compose run --rm --no-deps app php print-env.php)"
echo "$out2"
echo "$out2" | grep -q 'APP_ENV=staging'
IMAGE_ID2="$(docker images ch8-exercise-env:demo --format '{{.ID}}' | head -1)"
[[ "$IMAGE_ID" == "$IMAGE_ID2" ]] || { echo "Image ID changed after env edit (unexpected rebuild?)" >&2; exit 1; }
echo "OK: same image ID after .env.docker edit"

echo ""
echo "=== 9. Compose requires DB_PASSWORD ==="
mv .env .env.bak
cfg_err="$(docker compose config 2>&1)" || true
echo "$cfg_err" | head -3
echo "$cfg_err" | grep -qi 'DB_PASSWORD' || { mv .env.bak .env; echo "Expected DB_PASSWORD in compose config error" >&2; exit 1; }
echo "OK: compose config fails when DB_PASSWORD missing"
mv .env.bak .env

docker compose down -v 2>/dev/null || true

echo ""
echo "Solution demo OK: wire env at runtime; no secrets baked in image."
