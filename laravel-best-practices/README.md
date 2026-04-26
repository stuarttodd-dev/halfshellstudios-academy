# Laravel best practices — exercise solutions

Reference material for the **PHP to Laravel** course on Half Shell Studios Academy. The course slug in the app remains `php-to-laravel`; this folder is named for clarity in the Academy repo.

## Runnable Laravel app in each chapter

**Every** `chNN-exercise-*/` folder (chapters 2–17) now includes a full **`laravel/`** sub-project generated from [\_laravel-skeleton](_laravel-skeleton/) (Laravel 13, PHP 8.2+). After `composer install` it is a normal, runnable app.

**You should not** run anything from the repo root `laravel-best-practices/`; **always** `cd` into a chapter, then into `laravel/`.

**First-time setup in one chapter (example, chapter 2):**

```bash
cd ch02-exercise-build-crud-routes/laravel
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

- Health: [http://127.0.0.1:8000/exercise](http://127.0.0.1:8000/exercise) should return `ok`.
- `vendor/`, `node_modules/`, `.env`, and `database/database.sqlite` are **gitignored**; after a fresh clone, run `composer install` in each `laravel/` you need (or `bash scripts/composer-install-all.sh`).

**Rebuilding `laravel/` from source** (after you edit a chapter’s `files/`): from `laravel-best-practices/`, run `php scripts/materialize_laravel_apps.php` then `composer install` in that chapter’s `laravel/` again. Maintainers: keep [\_laravel-skeleton](_laravel-skeleton/) in sync when upgrading the framework, then re-run the materialize script.

**Chapters 14–17** are still mostly **operational** exercises (runbooks, tests, Spatie); the `laravel/` app there has minimal routes and points you at `SOLUTION.md`.

## Source trees: `files/` and `laravel/`

- **`files/`** — the patch you would merge into a real app; edit here, then re-run the materialize script to refresh `laravel/`.
- **`laravel/`** — generated runnable project (do not hand-edit: changes get overwritten on rematerialize).

If you **prefer not** to use the bundled `laravel/`, you can still copy from `files/` into your own Laravel project, one chapter at a time (avoid mixing multiple chapters in one app without resolving duplicate class names such as `User` or `Post`).

## Chapters 2–17 (end-of-chapter exercises)

| Ch | Topic | Learn URL | This repo |
| --- | --- | --- | --- |
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

**View on GitHub** (when wired from `code_examples_repo` in `course-template`) points at this course folder. Run the code from each chapter’s `laravel/` subfolder after `composer install`, as in the [example above](#runnable-laravel-app-in-each-chapter).

← [Half Shell Studios Academy](../README.md)
