# Chapter 15 — Exercise: queues (reference write-up)

**Course page:** [exercise-build-queue-system](https://laravel.learnio.dev/learn/sections/chapter-15-queues-and-horizon/exercise-build-queue-system)

The hand-in is a **description** of a queue-backed feature (idempotency, retries, monitoring). See **[SOLUTION.md](SOLUTION.md)** for a sample deliverable.

## Run the bundled app (optional)

From `laravel-best-practices/`:

```bash
cd ch15-exercise-build-queue-system
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8015
```


## How to test (lesson)

1. **Health:** `GET /exercise` → `ok`.
2. If you add a real job in your own branch: run `php artisan queue:work` locally, dispatch the job, verify **Horizon** / logs as the lesson describes (this repo’s scaffold is intentionally thin).
