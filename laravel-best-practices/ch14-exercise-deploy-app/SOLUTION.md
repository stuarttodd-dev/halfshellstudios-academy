# Chapter 14 — Exercise: one deploy you can hand to a teammate (solution / runbook)

**Course page:** [Ship one release you can explain](https://laravel.learnio.dev/learn/sections/chapter-14-vite-deploy/exercise-deploy-app)

The lesson asks for a runbook, evidence, and a practice rollback, not a single PHP class. This file is a **template** you can copy into your project wiki or `docs/deploy.md` and fill in with real host names and commit SHAs.

---

## 0. Pre-flight (local, clean tree)

1. `git status` (clean) and note commit SHA: `________________`.
2. `composer install --no-dev --optimize-autoloader`
3. `npm ci` then `npm run build`
4. Confirm `public/build/manifest.json` (or Vite manifest) and hashed assets exist.
5. Run `php artisan test` (or your minimum smoke suite).

**Failure rule:** do not deploy until the above is green.

---

## 1. On the server (order matters for Laravel)

1. Check out the **same** commit as step 0 (or the release tag you cut from it).
2. Install PHP deps: `composer install --no-dev --optimize-autoloader` (or your container build step that does the equivalent).
3. Copy `.env` from `.env.example` if first deploy, then set at least: `APP_KEY` (or `php artisan key:generate` once), `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_*`, `QUEUE_CONNECTION`, any mail/Redis keys the app needs.
4. `php artisan migrate --force`
5. Optional but common: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` (in the order your team agrees; if you use closure-only routes, **skip** `route:cache` until you confirm compatibility).
6. `php artisan queue:restart` if you run workers.
7. Restart PHP-FPM / Octane / Horizon as your platform requires.

**Web server:** document root must point at `public/`, not the repo root.

---

## 2. Verify

1. `curl -sI https://your-app.example` — expect `200` on `/` (or a documented redirect).
2. In the browser, hard-refresh. Open DevTools → Network: a CSS/JS file from `public/build/...` should return `200` and match the build from step 0.
3. Optional: one authenticated smoke path (login) if production allows.

**Evidence to save:** HAR, screenshot, or log line with timestamp and path.

---

## 3. Rollback drill (staging only in production-minded teams)

1. Re-deploy the **previous** release commit or re-point the symlink / scale the previous image.
2. Re-run the same `curl` and asset checks.
3. If the database migrated forward, document whether you also need a down migration (often you **do not** roll back schema in prod without a plan).

---

## 4. “Done” checklist (from the course)

- [ ] A teammate can run through sections 0–2 without phoning you.
- [ ] You recorded which commit and which `public/build` fingerprint shipped.
- [ ] You know the first log file you would open if the page is white (edge vs PHP-FPM log vs `storage/logs`).

This folder intentionally has no PHP source: the learning outcome is **operational**, not a new controller.
