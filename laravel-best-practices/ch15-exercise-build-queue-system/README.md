# Chapter 15 — Exercise: queues (reference write-up)

**Course page:** [exercise-build-queue-system](https://laravel.learnio.dev/learn/sections/chapter-15-queues-and-horizon/exercise-build-queue-system)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — this chapter is **narrative-first**; follow **[SOLUTION.md](SOLUTION.md)** for a complete write-up style.

The hand-in is a **description** of a queue-backed feature (idempotency, retries, monitoring). See **[SOLUTION.md](SOLUTION.md)** for a sample deliverable.

### Lesson acceptance (course)

- A **concrete job** (name it per your hand-in) with an **idempotent** `handle` (retries do not double-apply business effects) — the sample in **SOLUTION.md** shows the argument structure.
- **Local commands** you can run: `php artisan queue:work`, inspect **failed** jobs, and **retry** after a fix.
- **What you would monitor** in production (failed-job rate, latency, or Horizon — as the course asks).

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


---

## How to test everything

**Port:** `8015`. The hand-in is a **written** description of a queue-backed feature (idempotency, failures, `QUEUE_CONNECTION`, Horizon if applicable). The bundled app is a **thin** scaffold; operational detail lives in **[SOLUTION.md](SOLUTION.md)**.

| Step | Check |
| ---- | ----- |
| 0 | (Optional) [Run the bundled app](#run-the-bundled-app-optional) — **8015** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /chapter-15` — confirms HTTP surface |
| 3 | In **SOLUTION.md** / your write-up: **worker command** (or supervisor), **how you spot poison messages**, and **idempotency** for your job (what happens on retry) |
| 4 | (On your own machine) Run `php artisan queue:work` (or your chosen approach) against the sample job(s) in this repo if present — *if the scaffold has no real job, state that in your doc and test in a branch where you add one* |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8015/exercise"
```

**2 — Chapter pointer**

```bash
curl -sS "http://127.0.0.1:8015/chapter-15"
```
