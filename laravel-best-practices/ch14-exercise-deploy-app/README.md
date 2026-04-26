# Chapter 14 — Exercise: deploy (reference runbook)

**Course page:** [exercise-deploy-app](https://laravel.learnio.dev/learn/sections/chapter-14-vite-deploy/exercise-deploy-app)

The learning outcome is **operational** (runbook, evidence, rollback narrative), not a large amount of new PHP. See **[SOLUTION.md](SOLUTION.md)** for a completed runbook template.

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


## How to test (lesson)

1. **Health:** `GET /exercise` → `ok`.
2. Follow **SOLUTION.md** / the course: document build, env, migrate, `queue:restart`, smoke checks, and rollback steps for *your* host — the repo cannot run your production for you.
