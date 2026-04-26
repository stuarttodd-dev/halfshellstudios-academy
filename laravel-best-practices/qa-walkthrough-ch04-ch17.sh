#!/usr/bin/env bash
# One-shot QA: follow each chapter README (ch4–ch17) against 127.0.0.1. Destroys per-app SQLite and re-migrates.
set -euo pipefail
BASE="$(cd "$(dirname "$0")" && pwd)"
cd "$BASE" || exit 1

log() { printf '\n== %s ==\n' "$*"; }
fail() { echo "FAIL: $*" >&2; exit 1; }

start_serve() {
  local port="$1"
  php artisan serve --host=127.0.0.1 --port="$port" >/tmp/laravel-qa-serve.log 2>&1 &
  SERVE_PID=$!
  sleep 2
}

stop_serve() {
  if [ -n "${SERVE_PID:-}" ]; then
    kill "$SERVE_PID" 2>/dev/null || true
    wait "$SERVE_PID" 2>/dev/null || true
    SERVE_PID=""
  fi
}

# --- ch4: 8004, seed
log "ch4 — checkout (8004)"
cd "$BASE/ch04-exercise-validate-complex-form/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
start_serve 8004
CJ="/tmp/qa-cj-4"
rm -f "$CJ"
[ "$(curl -sS "http://127.0.0.1:8004/exercise")" = "ok" ] || fail "ch4 /exercise"
curl -sS -c "$CJ" -b "$CJ" "http://127.0.0.1:8004/_exercise/login" >/dev/null
OUT="$(curl -sS -X POST -b "$CJ" "http://127.0.0.1:8004/checkout" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"Alex","email":"alex@example.com","account_type":"personal","items":[{"product_id":1,"quantity":2}]}')"
echo "$OUT" | grep -q "received" || fail "ch4 happy path: $OUT"
C422=$(curl -sS -o /dev/null -w '%{http_code}' -X POST -b "$CJ" "http://127.0.0.1:8004/checkout" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"A","email":"not-an-email","account_type":"personal","items":[{"product_id":1,"quantity":1}]}')
[ "$C422" = "422" ] || fail "ch4 bad email expected 422 got $C422"
C403=$(curl -sS -o /dev/null -w '%{http_code}' -X POST "http://127.0.0.1:8004/checkout" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"A","email":"a@a.com","account_type":"personal","items":[{"product_id":1,"quantity":1}]}')
[ "$C403" = "403" ] || fail "ch4 guest expected 403 got $C403"
stop_serve
echo "ch4 OK"

# --- ch5: 8005, seed
log "ch5 — dashboard (8005)"
cd "$BASE/ch05-exercise-build-dashboard-ui/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
start_serve 8005
[ "$(curl -sS "http://127.0.0.1:8005/exercise")" = "ok" ] || fail "ch5 /exercise"
HTML="$(curl -sS "http://127.0.0.1:8005/dashboard" | head -c 200)"
echo "$HTML" | grep -qiE 'DOCTYPE|html' || fail "ch5 dashboard not HTML: $HTML"
stop_serve
echo "ch5 OK"

# --- ch6: 8006
log "ch6 — model (8006)"
cd "$BASE/ch06-exercise-build-model-layer/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8006
[ "$(curl -sS "http://127.0.0.1:8006/exercise")" = "ok" ] || fail "ch6 /exercise"
[ "$(curl -sS "http://127.0.0.1:8006/posts-demo")" = '{"count":0}' ] || fail "ch6 count 0"
php artisan tinker --execute='$u = \App\Models\User::factory()->create(); \App\Models\Post::query()->create(["user_id" => $u->id, "title" => "Demo", "slug" => "demo-".time(), "body" => "Hi", "is_published" => 1, "published_at" => now()]);' >/dev/null
echo "$(curl -sS "http://127.0.0.1:8006/posts-demo")" | grep -q '"count":1' || fail "ch6 count not 1"
stop_serve
echo "ch6 OK"

# --- ch7: 8007
log "ch7 — relations (8007)"
cd "$BASE/ch07-exercise-build-relational-data-model/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8007
[ "$(curl -sS "http://127.0.0.1:8007/exercise")" = "ok" ] || fail "ch7 /exercise"
php artisan tinker --execute='$u = \App\Models\User::factory()->create(); $tag = \App\Models\Tag::query()->create(["name" => "Laravel", "slug" => "laravel-".time()]); $p = \App\Models\Post::query()->create(["user_id" => $u->id, "title" => "Relational post", "slug" => "post-".time(), "body" => "Body", "published_at" => now()]); $p->tags()->attach($tag->id); \App\Models\Comment::query()->create(["post_id" => $p->id, "author_name" => "Sam", "content" => "Nice"]);' >/dev/null
HTML2="$(curl -sS "http://127.0.0.1:8007/posts" | head -c 400)"
echo "$HTML2" | grep -qiE '<ul|Relational|Laravel' || fail "ch7 /posts unexpected: $HTML2"
stop_serve
echo "ch7 OK"

# --- ch8: 8008, seed
log "ch8 — queries (8008)"
cd "$BASE/ch08-exercise-optimise-queries/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
start_serve 8008
[ "$(curl -sS "http://127.0.0.1:8008/exercise")" = "ok" ] || fail "ch8 /exercise"
J="$(curl -sS -H "Accept: application/json" "http://127.0.0.1:8008/admin/orders")"
echo "$J" | head -c 1 | grep -qE '\[|{' || fail "ch8 admin/orders: $J"
R="$(curl -sS -H "Accept: application/json" "http://127.0.0.1:8008/reports/monthly-revenue")"
echo "$R" | grep -q '"data"' || fail "ch8 report: $R"
stop_serve
echo "ch8 OK"

# --- ch9: 8009, seed
log "ch9 — seed (8009)"
cd "$BASE/ch09-exercise-seed-complex-dataset/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
start_serve 8009
[ "$(curl -sS "http://127.0.0.1:8009/exercise")" = "ok" ] || fail "ch9 /exercise"
echo "$(curl -sS "http://127.0.0.1:8009/seed-demo")" | grep -q "db:seed" || fail "ch9 /seed-demo"
OUT9="$(php artisan tinker --execute="print_r(\\Illuminate\\Support\\Facades\\DB::table('roles')->pluck('slug')->all());" 2>&1)"
echo "$OUT9" | grep -q "owner" || fail "ch9 roles: $OUT9"
stop_serve
echo "ch9 OK"

# --- ch10: 8010
log "ch10 — auth (8010)"
cd "$BASE/ch10-exercise-build-auth-system/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8010
[ "$(curl -sS "http://127.0.0.1:8010/exercise")" = "ok" ] || fail "ch10 /exercise"
CJ10="/tmp/qa-cj-10"
# + in x-www-form-urlencoded is decoded as space; don’t use unencoded + in email for curl.
E="learner.$(date +%s)@example.com"
rm -f "$CJ10"
# Do not use -X POST with -L (curl re-POSTs to GET-only routes -> 405). Two-hop matches README.
C_REG=$(curl -sS -c "$CJ10" -b "$CJ10" -o /dev/null -w '%{http_code}' -H "Content-Type: application/x-www-form-urlencoded" --data "name=Learner&email=$E&password=password1&password_confirmation=password1" "http://127.0.0.1:8010/register")
[ "$C_REG" = "302" ] || fail "ch10 register expected 302, got $C_REG"
C_DASH=$(curl -sS -b "$CJ10" -o /dev/null -w '%{http_code}' "http://127.0.0.1:8010/dashboard")
[ "$C_DASH" = "200" ] || fail "ch10 dashboard $C_DASH"
php artisan test --filter=AuthenticationTest
stop_serve
echo "ch10 OK"

# --- ch11: 8011
log "ch11 — policies (8011)"
cd "$BASE/ch11-exercise-build-role-system/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8011
[ "$(curl -sS "http://127.0.0.1:8011/exercise")" = "ok" ] || fail "ch11 /exercise"
CJ11="/tmp/qa-cj-11"
rm -f "$CJ11"
curl -sS -c "$CJ11" -b "$CJ11" "http://127.0.0.1:8011/_exercise/login" >/dev/null
PLIST="$(curl -sS -b "$CJ11" "http://127.0.0.1:8011/projects")"
PID=$(echo "$PLIST" | python3 -c "import json,sys; a=json.load(sys.stdin); print(a[0]['id'] if a else '')" 2>/dev/null || true)
[ -n "$PID" ] || fail "ch11 no project id from $PLIST"
PT="$(curl -sS -b "$CJ11" -H "Accept: application/json" -H "Content-Type: application/json" -X PATCH "http://127.0.0.1:8011/projects/$PID" -d '{"title":"QA Renamed"}')"
echo "$PT" | grep -q "QA Renamed" || fail "ch11 patch: $PT"
php artisan test --filter=ProjectPolicyTest
stop_serve
echo "ch11 OK"

# --- ch12: 8012
log "ch12 — strategy (8012)"
cd "$BASE/ch12-exercise-build-strategy-system/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8012
[ "$(curl -sS "http://127.0.0.1:8012/exercise")" = "ok" ] || fail "ch12 /exercise"
P12="$(curl -sS "http://127.0.0.1:8012/pricing-demo?subtotal=10000")"
echo "$P12" | grep -q "total_pence" || fail "ch12 pricing: $P12"
php artisan test --filter=PricingStrategyTest
stop_serve
echo "ch12 OK"

# --- ch13: 8013
log "ch13 — leads (8013)"
cd "$BASE/ch13-exercise-refactor-app/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8013
[ "$(curl -sS "http://127.0.0.1:8013/exercise")" = "ok" ] || fail "ch13 /exercise"
C201=$(curl -sS -o /dev/null -w '%{http_code}' -X POST "http://127.0.0.1:8013/leads" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"ACME","email":"buyer@acme.com","message":"We need a quote for 200 seats."}')
[ "$C201" = "201" ] || fail "ch13 leads 201 got $C201"
C422b=$(curl -sS -o /dev/null -w '%{http_code}' -X POST "http://127.0.0.1:8013/leads" -H "Content-Type: application/json" -H "Accept: application/json" -d '{"name":"X","email":"not-an-email","message":"short"}')
[ "$C422b" = "422" ] || fail "ch13 bad email 422 got $C422b"
stop_serve
echo "ch13 OK"

# --- ch14: 8014
log "ch14 — deploy scaffold (8014)"
cd "$BASE/ch14-exercise-deploy-app/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8014
[ "$(curl -sS "http://127.0.0.1:8014/exercise")" = "ok" ] || fail "ch14 /exercise"
echo "$(curl -sS "http://127.0.0.1:8014/chapter-14")" | head -c 5 | grep -qE . || fail "ch14 chapter-14 empty"
stop_serve
echo "ch14 OK"

# --- ch15: 8015
log "ch15 — queue scaffold (8015)"
cd "$BASE/ch15-exercise-build-queue-system/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8015
[ "$(curl -sS "http://127.0.0.1:8015/exercise")" = "ok" ] || fail "ch15 /exercise"
curl -sS "http://127.0.0.1:8015/chapter-15" | grep -qE . || fail "ch15 chapter-15 empty"
stop_serve
echo "ch15 OK"

# --- ch16: 8016
log "ch16 — test suite (8016)"
cd "$BASE/ch16-exercise-full-test-suite/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8016
[ "$(curl -sS "http://127.0.0.1:8016/exercise")" = "ok" ] || fail "ch16 /exercise"
curl -sS "http://127.0.0.1:8016/chapter-16" | grep -qE . || fail "ch16 chapter-16 empty"
php artisan test
stop_serve
echo "ch16 OK"

# --- ch17: 8017
log "ch17 — capstone scaffold (8017)"
cd "$BASE/ch17-exercise-build-roles-and-media/laravel"
[ -f .env ] || cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force
start_serve 8017
[ "$(curl -sS "http://127.0.0.1:8017/exercise")" = "ok" ] || fail "ch17 /exercise"
curl -sS "http://127.0.0.1:8017/chapter-17" | grep -qE . || fail "ch17 chapter-17 empty"
stop_serve
echo "ch17 OK"

log "All chapters 4–17 QA passed."
