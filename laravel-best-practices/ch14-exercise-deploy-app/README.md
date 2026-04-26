# Chapter 14 — Exercise: deploy (reference runbook)

**Course page:** [exercise-deploy-app](https://laravel.learnio.dev/learn/sections/chapter-14-vite-deploy/exercise-deploy-app)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — the **grade is your runbook**, not only the `laravel/` smoke app.

The learning outcome is **operational** (runbook, evidence, rollback narrative), not a large amount of new PHP. See **[SOLUTION.md](SOLUTION.md)** for a completed runbook template.

### Lesson acceptance (course)

- You can **deploy** the app to a real target (VPS, PaaS) using steps you would give a teammate — no missing env or migration order.
- You document **evidence** (build output, `php artisan about`, post-deploy `curl` / health) and a **rollback** path if a release fails.
- Optional: **Vite / asset** build in the pipeline, as the chapter teaches — describe where that runs.

## Run the bundled app (optional)

The `laravel/` folder is a minimal scaffold so you still have a working project if needed.

From `laravel-best-practices/`:

```bash
cd ch14-exercise-deploy-app
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8014
```


---

## How to test everything

**Port:** `8014`. This chapter is **ops-focused**: the runnable app is a **smoke** surface; the real deliverable is the **runbook and evidence** in **[SOLUTION.md](SOLUTION.md)** (deploy, health checks, rollback story).

| Step | Check |
| ---- | ----- |
| 0 | (Optional) Follow [Run the bundled app](#run-the-bundled-app-optional) — migrate + serve on **8014** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /chapter-14` — small JSON/response the scaffold exposes (proves the server is up) |
| 3 | Open **SOLUTION.md** — you should be able to **execute your runbook** on your target host (VPS, PaaS) without guessing missing steps |
| 4 | Cross-check: env vars, `php artisan config:cache` / `route:cache` story, and **how you roll back** if a deploy fails |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8014/exercise"
```

**2 — Chapter pointer**

```bash
curl -sS "http://127.0.0.1:8014/chapter-14"
```

*Vite / asset build / zero-downtime: document in your **SOLUTION.md**, not in this minimal scaffold.*
