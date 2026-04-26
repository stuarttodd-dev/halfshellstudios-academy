# Chapter 13 — Exercise: refactor a fat “contact sales” controller

**Course page:** [Refactor toward services, DTOs, and seams](https://laravel.learnio.dev/learn/sections/chapter-13-services-actions-dtos/exercise-refactor-app)

## Run the app

From `laravel-best-practices/`:

```bash
cd ch13-exercise-refactor-app
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8013
```


## What’s in the app

Under **`laravel/`**: `StoreLeadRequest`, DTO, `CreateLead` action, `CrmClient` contract + `HttpCrmClient` / `NullCrmClient`, `Lead` model + migration, `LeadController@store` thin, `config/services.php` (merge pattern in `config/SERVICES_CRM_SNIPPET.txt` if you extend), jobs/events as in the solution.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **POST /leads** (or the route in `routes/solution.php`) with valid JSON/body per `StoreLeadRequest` — expect 201/redirect; invalid payload → 422.
3. **Fakes in tests (lesson):** unit test `CreateLead` with a fake `CrmClient`; feature test with `Http::fake`, `Queue::fake`, `Event::fake` as in the course.
4. **Config:** `config('services.crm')` should resolve for the HTTP client; `.env` keys documented in the lesson.

## Course link

[exercise-refactor-app](https://laravel.learnio.dev/learn/sections/chapter-13-services-actions-dtos/exercise-refactor-app)
