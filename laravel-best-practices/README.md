# Laravel best practices — exercise solutions

Reference material for the **PHP to Laravel** course on Half Shell Studios Academy. The course slug in the app remains `php-to-laravel`; this folder is named for clarity in the Academy repo.

## Runnable Laravel app in each chapter

**Every** `chNN-exercise-*/` folder (chapters **1–17**) includes a full **`laravel/`** sub-project generated from [\_laravel-skeleton](_laravel-skeleton/) (Laravel 13, PHP 8.3+). After `composer install` it is a normal, runnable app. Chapter 1 is the [Laravel tour “Hello Laravel” mini-project](http://127.0.0.1:38080/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app) in [`ch01-exercise-hello-laravel-app`](ch01-exercise-hello-laravel-app/); **`_laravel-skeleton` is only the shared template**, not the chapter 1 lesson link target.

**You should not** run anything from the Academy repo root; work inside **`laravel-best-practices/`** (the folder that contains `ch01-exercise-...`, `ch02-exercise-...`, etc.).

### Prerequisites (install once on your machine)

- **PHP 8.3+** on your `PATH` (`php -v`) — Laravel 13 requires this; the skeleton’s `composer.json` uses `"php": "^8.3"`. Extensions typically required by Laravel: `ctype`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, and **PDO SQLite** (`pdo_sqlite`) so the default SQLite database works.
- **Composer 2** on your `PATH` — install from [getcomposer.org](https://getcomposer.org/download/), then check `composer -V`.
- **Node.js** (LTS) and **npm** are optional: only needed if you run Vite/frontend builds (`npm install`, `npm run build`) in an app. Most chapter exercises are fine with `php artisan serve` alone.

`vendor/`, `node_modules/`, `.env`, and `database/database.sqlite` are **not** committed. After a fresh clone, each `laravel/` app needs `composer install` (see [per-chapter paths](#per-chapter-where-to-cd-and-extra-commands) below).

### Per-chapter: where to cd and extra commands

All paths are relative to `laravel-best-practices/`. The **`cd` target** column is the folder you `cd` into (it always ends in `…/laravel`).

| Ch | `cd` target | After `php artisan migrate --force` |
| --- | --- | --- |
| 1 | `ch01-exercise-hello-laravel-app/laravel` | — |
| 2 | `ch02-exercise-build-crud-routes/laravel` | — |
| 3 | `ch03-exercise-build-access-control-layer/laravel` | — |
| 4 | `ch04-exercise-validate-complex-form/laravel` | `php artisan db:seed` (sample user + product for checkout) |
| 5 | `ch05-exercise-build-dashboard-ui/laravel` | `php artisan db:seed` (sample user) |
| 6 | `ch06-exercise-build-model-layer/laravel` | — |
| 7 | `ch07-exercise-build-relational-data-model/laravel` | — |
| 8 | `ch08-exercise-optimise-queries/laravel` | `php artisan db:seed` (user + order data for reports) |
| 9 | `ch09-exercise-seed-complex-dataset/laravel` | `php artisan db:seed` (main exercise) |
| 10 | `ch10-exercise-build-auth-system/laravel` | — (optional: `php artisan test` for `AuthenticationTest`) |
| 11 | `ch11-exercise-build-role-system/laravel` | — (optional: `php artisan test` for `ProjectPolicyTest`) |
| 12 | `ch12-exercise-build-strategy-system/laravel` | — (optional: `php artisan test` for `PricingStrategyTest`) |
| 13 | `ch13-exercise-refactor-app/laravel` | — |
| 14 | `ch14-exercise-deploy-app/laravel` | **Thin** scaffold — exercise is the deploy runbook; see `SOLUTION.md` |
| 15 | `ch15-exercise-build-queue-system/laravel` | **Thin** scaffold — queue write-up; see `SOLUTION.md` |
| 16 | `ch16-exercise-full-test-suite/laravel` | **Thin** scaffold — CI/testing write-up; see `SOLUTION.md` |
| 17 | `ch17-exercise-build-roles-and-media/laravel` | **Thin** scaffold — Spatie install steps in `SOLUTION.md` (not a full package demo in this repo) |

### First-time setup for **one** app (repeat for every chapter you open)

**Always** start from the **`cd` target** for that chapter in the [table above](#per-chapter-where-to-cd-and-extra-commands), then run:

```bash
# From: laravel-best-practices/
cd ch01-exercise-hello-laravel-app/laravel   # example — use the "cd" target from the table for your chapter

cp -n .env.example .env
composer install
php artisan key:generate
php artisan migrate --force
# If the table says to seed, run:
# php artisan db:seed

php artisan serve
```

- Quick health check when the server is up: [http://127.0.0.1:8000/exercise](http://127.0.0.1:8000/exercise) should return `ok`.
- **Stop the server** with `Ctrl+C`, then `cd` to another chapter’s `laravel/` and run the same block again. Each app is independent (its own `vendor/`, `.env`, and `database/database.sqlite`).

**Default database:** `DB_CONNECTION=sqlite` in `.env.example`; SQLite file is `laravel/database/database.sqlite` (created by the materialize script, or on first migrate). You do not need MySQL/Postgres for these exercises.

**Reset an app’s database:** from that app’s `laravel/` folder, e.g. `rm -f database/database.sqlite && touch database/database.sqlite && php artisan migrate --force` (add `db:seed` if that chapter uses seeders — see table).

**Install PHP dependencies in every chapter’s app at once** (optional, uses a lot of disk and time). From `laravel-best-practices/`:

```bash
bash scripts/composer-install-all.sh
```

You still need **`cp` / `.env`**, **`php artisan key:generate`**, and **`php artisan migrate`** in each `laravel/` you actually run; Composer does not do those.

**Rebuilding `laravel/` from source** (after you edit a chapter’s `files/`): from `laravel-best-practices/`, run `php scripts/materialize_laravel_apps.php` then `composer install` in that chapter’s `laravel/` again. Maintainers: keep [\_laravel-skeleton](_laravel-skeleton/) in sync when upgrading the framework, then re-run the materialize script.

## Source trees: `files/` and `laravel/`

- **`files/`** — the patch you would merge into a real app; edit here, then re-run the materialize script to refresh `laravel/`.
- **`laravel/`** — generated runnable project (do not hand-edit: changes get overwritten on rematerialize).

If you **prefer not** to use the bundled `laravel/`, you can still copy from `files/` into your own Laravel project, one chapter at a time (avoid mixing multiple chapters in one app without resolving duplicate class names such as `User` or `Post`).

## Chapters 1–17 (tour + end-of-chapter exercises)

| Ch | Topic | Learn URL | This repo |
| --- | --- | --- | --- |
| 1 | Hello Laravel (tour) | [mini-project](http://127.0.0.1:38080/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app) | [ch01-exercise-hello-laravel-app](ch01-exercise-hello-laravel-app/) |
| 2 | CRUD routes | [exercise](http://127.0.0.1:38080/learn/sections/chapter-2-routing-controllers-request/exercise-build-crud-routes) | [ch02-exercise-build-crud-routes](ch02-exercise-build-crud-routes/) |
| 3 | Access control (middleware) | [exercise](http://127.0.0.1:38080/learn/sections/chapter-3-middleware/exercise-build-access-control-layer) | [ch03-exercise-build-access-control-layer](ch03-exercise-build-access-control-layer/) |
| 4 | Complex form (Form Request) | [exercise](http://127.0.0.1:38080/learn/sections/chapter-4-validation-form-requests/exercise-validate-complex-form) | [ch04-exercise-validate-complex-form](ch04-exercise-validate-complex-form/) |
| 5 | Dashboard (Blade) | [exercise](http://127.0.0.1:38080/learn/sections/chapter-5-blade-and-frontend-choice/exercise-build-dashboard-ui) | [ch05-exercise-build-dashboard-ui](ch05-exercise-build-dashboard-ui/) |
| 6 | `Post` model layer | [exercise](http://127.0.0.1:38080/learn/sections/chapter-6-eloquent-models-migrations/exercise-build-model-layer) | [ch06-exercise-build-model-layer](ch06-exercise-build-model-layer/) |
| 7 | Relations + list query | [exercise](http://127.0.0.1:38080/learn/sections/chapter-7-relations/exercise-build-relational-data-model) | [ch07-exercise-build-relational-data-model](ch07-exercise-build-relational-data-model/) |
| 8 | Query optimisation | [exercise](http://127.0.0.1:38080/learn/sections/chapter-8-query-builder-vs-eloquent/exercise-optimise-queries) | [ch08-exercise-optimise-queries](ch08-exercise-optimise-queries/) |
| 9 | Demo seed / factories | [exercise](http://127.0.0.1:38080/learn/sections/chapter-9-factories-seeders-transactions/exercise-seed-complex-dataset) | [ch09-exercise-seed-complex-dataset](ch09-exercise-seed-complex-dataset/) |
| 10 | Auth (web + tests) | [exercise](http://127.0.0.1:38080/learn/sections/chapter-10-authentication/exercise-build-auth-system) | [ch10-exercise-build-auth-system](ch10-exercise-build-auth-system/) |
| 11 | Org + role + policy | [exercise](http://127.0.0.1:38080/learn/sections/chapter-11-authorisation/exercise-build-role-system) | [ch11-exercise-build-role-system](ch11-exercise-build-role-system/) |
| 12 | Strategy + container | [exercise](http://127.0.0.1:38080/learn/sections/chapter-12-service-container-and-providers/exercise-build-strategy-system) | [ch12-exercise-build-strategy-system](ch12-exercise-build-strategy-system/) |
| 13 | Refactor (lead / action) | [exercise](http://127.0.0.1:38080/learn/sections/chapter-13-services-actions-dtos/exercise-refactor-app) | [ch13-exercise-refactor-app](ch13-exercise-refactor-app/) |
| 14 | Deploy runbook | [exercise](http://127.0.0.1:38080/learn/sections/chapter-14-vite-deploy/exercise-deploy-app) | [ch14-exercise-deploy-app](ch14-exercise-deploy-app/) |
| 15 | Queues (job + write-up) | [exercise](http://127.0.0.1:38080/learn/sections/chapter-15-queues-and-horizon/exercise-build-queue-system) | [ch15-exercise-build-queue-system](ch15-exercise-build-queue-system/) |
| 16 | Test suite + CI | [exercise](http://127.0.0.1:38080/learn/sections/chapter-16-testing-laravel/exercise-full-test-suite) | [ch16-exercise-full-test-suite](ch16-exercise-full-test-suite/) |
| 17 | Spatie roles + media | [exercise](http://127.0.0.1:38080/learn/sections/chapter-17-spatie-packages/build-a-roles-and-media-feature) | [ch17-exercise-build-roles-and-media](ch17-exercise-build-roles-and-media/) |

## Course site and GitHub

**View on GitHub** (when wired from `code_examples_repo` in `course-template`) points at this course folder. For each exercise app, use the [prerequisites and commands](#runnable-laravel-app-in-each-chapter) and the [per-chapter `cd` paths](#per-chapter-where-to-cd-and-extra-commands).

← [Half Shell Studios Academy](../README.md)
