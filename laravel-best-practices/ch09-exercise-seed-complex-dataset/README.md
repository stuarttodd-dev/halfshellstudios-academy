# Chapter 9 — Exercise: seed a complex demo dataset

**Course page:** [Build a multi-tenant demo with factories and seeders](https://laravel.learnio.dev/learn/sections/chapter-9-factories-seeders-transactions/exercise-seed-complex-dataset)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — the bundled **`DemoContentSeeder`** in this repo is a **skeleton** (the heavy factory graph is left commented for you to wire in as you follow the course). The **`role` table is populated**; other tables stay empty until you complete the seeder the lesson wants.

## Run the app

The exercise is the **seed** itself — the steps below run migrate then seed.

From `laravel-best-practices/`:

```bash
cd ch09-exercise-seed-complex-dataset
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force
php artisan serve --host=127.0.0.1 --port=8009
```


## What’s in the app

Under **`laravel/database/`** (seeders, factories) and related models/migrations: `RoleSeeder`, `DemoContentSeeder`, `DatabaseSeeder` orchestration, factories for `Organisation`, `Project`, `Task`, `Tag`, etc. Aligns with the lesson’s shape: idempotent role seed, `DB::transaction` where useful, volume constant for task counts.

### Lesson acceptance (course)

- **Orchestrated seeder** — `DatabaseSeeder` (and any split seeders) run without exceptions on a fresh database.
- **Factories + idempotence** as the video/text asks: e.g. **roles** can be re-seeded safely; when you **uncomment / implement** the demo org graph, wrap it in a **transaction** where the lesson does.
- You can point at **where the volume for tasks** is controlled (see `DEMO_TASKS_PER_PROJECT` in `DemoContentSeeder` when you use it).

**Do not use this sample’s empty `DemoContentSeeder` body as “proof the dataset exists”** — expand it in your branch per the course, or check `DB::table('roles')->count()` after seeding to confirm at least the role seeder did work.

---

## How to test everything

**Port:** `8009`. The main deliverable is running **`db:seed`** and verifying the **exercise** you implement (this repo’s `DemoContentSeeder` body may be empty until you add it; **`RoleSeeder` still runs**).

| Step | Check |
| ---- | ----- |
| 0 | Migrated, server **8009** (Run block already runs seed before `serve` — you can re-seed from `laravel/` any time) |
| 1 | `/exercise` → `ok` |
| 2 | `GET /seed-demo` — JSON nudge to run `artisan db:seed` |
| 3 | `php artisan db:seed --force` — completes with **0** exit code; no unhandled exception |
| 4 | (Optional) After seed, `roles` has slugs like `owner`, `member`, `viewer` (see `database/seeders/RoleSeeder.php`) |
| 5 | Re-run `db:seed` if your lesson requires **idempotency** — it should not blind-insert duplicates where your seeder is meant to be safe |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8009/exercise"
```

**2 — Seeder “banner” (JSON)**

```bash
curl -sS "http://127.0.0.1:8009/seed-demo"
```

**3 — The exercise (seeder) — from `ch09-exercise-seed-complex-dataset/laravel/`**

```bash
cd ch09-exercise-seed-complex-dataset/laravel && php artisan db:seed --force
```

**4 — (Optional) proof `RoleSeeder` ran** — you should see three slugs in the `roles` table:

```bash
cd ch09-exercise-seed-complex-dataset/laravel && php artisan tinker --execute="print_r(\\Illuminate\\Support\\Facades\\DB::table('roles')->pluck('slug')->all());"
```

**5 — Read the wiring**

- `database/seeders/DatabaseSeeder.php` — order of `call`s / transactions.
- `database/factories/*` and `database/seeders/*` — match the course’s multi-tenant shape.
