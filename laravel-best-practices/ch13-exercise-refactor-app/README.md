# Chapter 13 — Exercise: refactor a fat “contact sales” controller

**Course page:** [Refactor toward services, DTOs, and seams](https://laravel.learnio.dev/learn/sections/chapter-13-services-actions-dtos/exercise-refactor-app)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — `POST /leads` is **CSRF-exempt** here for smoke tests; see [CSRF in exercise apps](../README.md#csrf-in-exercise-apps).

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

### Lesson acceptance (course)

- **Controller stays thin** — `LeadController@store` mostly validates + calls an **action** / use-case class.
- **Seams:** a **CRM client interface** (fake vs HTTP) so the domain does not new-up Guzzle in the controller.
- **API:** **201** + `id` on success; **422** on bad input; persistence of the lead in SQLite for this sample.

---

## How to test everything

**Browser:** Open **`/exercise`** in the **browser** (step 1). **`POST /leads`** is JSON-only in this walkthrough—use **`curl`** or a REST client (or build a public form in your own work with CSRF). [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8013`. The `leads` route is **CSRF-exempt** in this exercise’s `bootstrap/app.php` so a plain `curl` `POST` works for smoke tests. In production you would **not** disable CSRF for public forms.

| Step | Check |
| ---- | ----- |
| 0 | Migrated, server **8013** |
| 1 | `/exercise` → `ok` |
| 2 | Valid `POST /leads` (JSON) — **201** and body `{"id":…}` |
| 3 | Invalid body (missing/ bad email) — **422** with validation errors (with `Accept: application/json`) |
| 4 | (Optional) `php artisan tinker` — `Lead::count()` increases after a 201 |
| 5 | Read `app/Http/Controllers/LeadController.php`, `App\Actions\Sales\CreateLead`, and the CRM client binding |

**1 — Health**

In the browser, open **`http://127.0.0.1:8013/exercise`**. Expect **`ok`**.

*Optional (terminal):* `curl -sS "http://127.0.0.1:8013/exercise"`

**2 — Create a lead (expect 201 + id)**

```bash
curl -sS -w "\nHTTP:%{http_code}\n" -X POST "http://127.0.0.1:8013/leads" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"name":"ACME","email":"buyer@acme.com","message":"We need a quote for 200 seats."}'
```

**3 — Validation error (example: bad email)**

```bash
curl -sS -w "\nHTTP:%{http_code}\n" -X POST "http://127.0.0.1:8013/leads" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"name":"X","email":"not-an-email","message":"short"}'
```

## Course link

[exercise-refactor-app](https://laravel.learnio.dev/learn/sections/chapter-13-services-actions-dtos/exercise-refactor-app)
