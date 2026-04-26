# Chapter 15 — Exercise: queues (reference write-up)

**Course page:** [exercise-build-queue-system](http://127.0.0.1:38080/learn/sections/chapter-15-queues-and-horizon/exercise-build-queue-system)

The hand-in is a **description** of a queue-backed feature (idempotency, retries, monitoring). See **[SOLUTION.md](SOLUTION.md)** for a sample deliverable.

## Run the bundled app (optional)

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch15-exercise-build-queue-system`** and port **8015**.

## How to test (lesson)

1. **Health:** `GET /exercise` → `ok`.
2. If you add a real job in your own branch: run `php artisan queue:work` locally, dispatch the job, verify **Horizon** / logs as the lesson describes (this repo’s scaffold is intentionally thin).
