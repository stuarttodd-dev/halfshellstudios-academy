# Laravel best practices — exercise solutions

Reference material for the **PHP to Laravel** course on Half Shell Studios Academy. The course slug in the app remains `php-to-laravel`; this folder is named for clarity in the Academy repo.

## Runnable app in each chapter

Each `chNN-exercise-*/laravel/` directory is a **complete Laravel 13 application** (PHP 8.3+). You run the app from there after setup.

Many chapters also include a **`files/`** directory: the **same paths** as the app root (`app/`, `routes/`, `resources/`, …), as a **reference** copy alongside the runnable app in **`laravel/`**.

**You should not** run anything from the Academy repo root; work inside **`laravel-best-practices/`**.

[`_laravel-skeleton`](_laravel-skeleton/) is only a **template** for **new** exercise folders (copy it to `chNN-exercise-*/laravel/` and match the patterns in existing chapters). It is **not** the chapter 1 “solution” by itself; use [`ch01-exercise-hello-laravel-app`](ch01-exercise-hello-laravel-app/).

### Dev ports

Chapter **1** → **8001**, **2** → **8002**, …, **17** → **8017** (**8000 + chapter number**). The [table below](#per-chapter-port-cd-targets-and-seed) lists each folder and whether to run `db:seed` after migrate.

### Setup one chapter app

From `laravel-best-practices/`, with your **chapter folder** and **port** = `8000 + chapter#` (e.g. ch **4** → folder `ch04-exercise-validate-complex-form`, port **8004**):

```bash
cd ch04-exercise-validate-complex-form    # use the folder from the table
[ -d files ] && rsync -a files/ laravel/  # if this chapter has files/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
# php artisan db:seed --force            # only if the table says to seed (ch4,5,8,9)
php artisan serve --host=127.0.0.1 --port=8004
```

**Health check:** `http://127.0.0.1:<port>/exercise` → `ok` (use that chapter’s port). Each **chapter’s `README.md`** includes the same setup **with that chapter’s folder, port, and `db:seed` when needed**; a short **prerequisites** line, **“Lesson acceptance (course)”** (maps to the hand-in), and **“How to test everything”** (ordered checks you can run locally).

### CSRF in exercise apps

A few chapters relax CSRF for specific routes in `laravel/bootstrap/app.php` so state-changing `curl` examples work without a token. **Do not** copy that pattern to production; it is for local **learning and smoke tests** only.

**Install Composer deps in every chapter at once** (optional, large download):

```bash
for d in ch*-exercise-*/laravel; do (cd "$d" && composer install --no-interaction); done
```

You still need **`.env`**, **key**, **migrate** (and **seed** where the table says) in each `laravel/` you care about.

### Prerequisites (install once on your machine)

- **PHP 8.3+** (`php -v`) and extensions Laravel typically needs, including **PDO SQLite** for the default database.
- **Composer 2** — [getcomposer.org](https://getcomposer.org/download/), `composer -V`.
- **Node.js** / **npm** — optional; only if you run Vite builds in an app.

`vendor/`, `node_modules/`, `.env`, and `database/database.sqlite` are **gitignored** per app.

### Per-chapter: port, `cd` targets, and seed

Paths are relative to `laravel-best-practices/`. **Port** = `8000` + **Ch** (e.g. ch **12** → **8012**). Serve with `php artisan serve --host=127.0.0.1 --port=<port>` from that chapter’s `laravel/` (see [Setup one chapter app](#setup-one-chapter-app)).

| Ch | Port | `cd` into `…/laravel` | After migrate |
| --- | --- | --- | --- |
| 1 | 8001 | `ch01-exercise-hello-laravel-app/laravel` | — |
| 2 | 8002 | `ch02-exercise-build-crud-routes/laravel` | — |
| 3 | 8003 | `ch03-exercise-build-access-control-layer/laravel` | — |
| 4 | 8004 | `ch04-exercise-validate-complex-form/laravel` | `php artisan db:seed` |
| 5 | 8005 | `ch05-exercise-build-dashboard-ui/laravel` | `php artisan db:seed` |
| 6 | 8006 | `ch06-exercise-build-model-layer/laravel` | — |
| 7 | 8007 | `ch07-exercise-build-relational-data-model/laravel` | — |
| 8 | 8008 | `ch08-exercise-optimise-queries/laravel` | `php artisan db:seed` |
| 9 | 8009 | `ch09-exercise-seed-complex-dataset/laravel` | `php artisan db:seed` |
| 10 | 8010 | `ch10-exercise-build-auth-system/laravel` | — (optional: `php artisan test --filter=AuthenticationTest`) |
| 11 | 8011 | `ch11-exercise-build-role-system/laravel` | — (optional: `php artisan test --filter=ProjectPolicyTest`) |
| 12 | 8012 | `ch12-exercise-build-strategy-system/laravel` | — (optional: `php artisan test --filter=PricingStrategyTest`) |
| 13 | 8013 | `ch13-exercise-refactor-app/laravel` | — |
| 14 | 8014 | `ch14-exercise-deploy-app/laravel` | **Thin** — [SOLUTION.md](ch14-exercise-deploy-app/SOLUTION.md) |
| 15 | 8015 | `ch15-exercise-build-queue-system/laravel` | **Thin** — [SOLUTION.md](ch15-exercise-build-queue-system/SOLUTION.md) |
| 16 | 8016 | `ch16-exercise-full-test-suite/laravel` | **Thin** — [SOLUTION.md](ch16-exercise-full-test-suite/SOLUTION.md) |
| 17 | 8017 | `ch17-exercise-build-roles-and-media/laravel` | **Thin** — [SOLUTION.md](ch17-exercise-build-roles-and-media/SOLUTION.md) |

**Default DB:** SQLite at `laravel/database/database.sqlite` (the setup block above `touch`es it if missing).

## Chapters 1–17 (tour + end-of-chapter exercises)

| Ch | Topic | Learn URL | This repo |
| --- | --- | --- | --- |
| 1 | Hello Laravel (tour) | [mini-project](https://laravel.learnio.dev/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app) | [ch01-exercise-hello-laravel-app](ch01-exercise-hello-laravel-app/) |
| 2 | CRUD routes | [exercise](https://laravel.learnio.dev/learn/sections/chapter-2-routing-controllers-request/exercise-build-crud-routes) | [ch02-exercise-build-crud-routes](ch02-exercise-build-crud-routes/) |
| 3 | Access control (middleware) | [exercise](https://laravel.learnio.dev/learn/sections/chapter-3-middleware/exercise-build-access-control-layer) | [ch03-exercise-build-access-control-layer](ch03-exercise-build-access-control-layer/) |
| 4 | Complex form (Form Request) | [exercise](https://laravel.learnio.dev/learn/sections/chapter-4-validation-form-requests/exercise-validate-complex-form) | [ch04-exercise-validate-complex-form](ch04-exercise-validate-complex-form/) |
| 5 | Dashboard (Blade) | [exercise](https://laravel.learnio.dev/learn/sections/chapter-5-blade-and-frontend-choice/exercise-build-dashboard-ui) | [ch05-exercise-build-dashboard-ui](ch05-exercise-build-dashboard-ui/) |
| 6 | `Post` model layer | [exercise](https://laravel.learnio.dev/learn/sections/chapter-6-eloquent-models-migrations/exercise-build-model-layer) | [ch06-exercise-build-model-layer](ch06-exercise-build-model-layer/) |
| 7 | Relations + list query | [exercise](https://laravel.learnio.dev/learn/sections/chapter-7-relations/exercise-build-relational-data-model) | [ch07-exercise-build-relational-data-model](ch07-exercise-build-relational-data-model/) |
| 8 | Query optimisation | [exercise](https://laravel.learnio.dev/learn/sections/chapter-8-query-builder-vs-eloquent/exercise-optimise-queries) | [ch08-exercise-optimise-queries](ch08-exercise-optimise-queries/) |
| 9 | Demo seed / factories | [exercise](https://laravel.learnio.dev/learn/sections/chapter-9-factories-seeders-transactions/exercise-seed-complex-dataset) | [ch09-exercise-seed-complex-dataset](ch09-exercise-seed-complex-dataset/) |
| 10 | Auth (web + tests) | [exercise](https://laravel.learnio.dev/learn/sections/chapter-10-authentication/exercise-build-auth-system) | [ch10-exercise-build-auth-system](ch10-exercise-build-auth-system/) |
| 11 | Org + role + policy | [exercise](https://laravel.learnio.dev/learn/sections/chapter-11-authorisation/exercise-build-role-system) | [ch11-exercise-build-role-system](ch11-exercise-build-role-system/) |
| 12 | Strategy + container | [exercise](https://laravel.learnio.dev/learn/sections/chapter-12-service-container-and-providers/exercise-build-strategy-system) | [ch12-exercise-build-strategy-system](ch12-exercise-build-strategy-system/) |
| 13 | Refactor (lead / action) | [exercise](https://laravel.learnio.dev/learn/sections/chapter-13-services-actions-dtos/exercise-refactor-app) | [ch13-exercise-refactor-app](ch13-exercise-refactor-app/) |
| 14 | Deploy runbook | [exercise](https://laravel.learnio.dev/learn/sections/chapter-14-vite-deploy/exercise-deploy-app) | [ch14-exercise-deploy-app](ch14-exercise-deploy-app/) |
| 15 | Queues (job + write-up) | [exercise](https://laravel.learnio.dev/learn/sections/chapter-15-queues-and-horizon/exercise-build-queue-system) | [ch15-exercise-build-queue-system](ch15-exercise-build-queue-system/) |
| 16 | Test suite + CI | [exercise](https://laravel.learnio.dev/learn/sections/chapter-16-testing-laravel/exercise-full-test-suite) | [ch16-exercise-full-test-suite](ch16-exercise-full-test-suite/) |
| 17 | Spatie roles + media | [exercise](https://laravel.learnio.dev/learn/sections/chapter-17-spatie-packages/build-a-roles-and-media-feature) | [ch17-exercise-build-roles-and-media](ch17-exercise-build-roles-and-media/) |

## Course site and GitHub

**View on GitHub** (when wired from `code_examples_repo` in `course-template`) points at this course folder.

← [Half Shell Studios Academy](../README.md)
