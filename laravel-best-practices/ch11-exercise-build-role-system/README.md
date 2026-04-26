# Chapter 11 — Exercise: org-scoped authorisation (policies)

**Course page:** [Build a B2B-style role and policy layer](https://laravel.learnio.dev/learn/sections/chapter-11-authorisation/exercise-build-role-system)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — use **`GET /_exercise/login` only in `local`**; production-like env will not expose that route. Replace hard-coded `1` in PATCH examples with an **`id` from your `GET /projects` JSON** so you are not 404/403 by accident.

## Run the app

From `laravel-best-practices/`:

```bash
cd ch11-exercise-build-role-system
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8011
```


## What’s in the app

Under **`laravel/`**: org + project migrations, `Organisation` / `Project` / `User` updates, `ProjectPolicy`, `ProjectController` using `$this->authorize`, `ProjectPolicyTest`, enums/routes as in the solution.

### Lesson acceptance (course)

- **Policies** gate `view` / `update` / `delete` (and any custom rules the course adds) for `Project` in an **org-scoped** way.
- **HTTP surface:** JSON routes for projects behave as the lesson table describes; a **forbidden** action returns **403** (see the bundled test for `delete` for an editor, as an example).

---

## How to test everything

> **Tip:** `http://127.0.0.1:…` links in this section are **Markdown** (click in your editor or on GitHub). **Curl** and other terminal steps use a fenced `bash` block per snippet—**select and copy the whole fence** in one go (all lines, including `\` line continuations).

**Browser (GET, after local login):** In **local** dev, open **`/exercise`**, then **`/_exercise/login`** in the same session, then **`/projects`** and **`/projects/{id}`** (you will see **JSON** in the tab; devtools or a JSON viewer help). **PATCH** and scripted checks still use **`curl`** below. [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8011`. `projects` and `projects/*` are **CSRF-exempt** in `bootstrap/app.php` (exercise only). A **session** (logged-in user) is required; the browser picks it up after **`GET /_exercise/login`**; for **cookie-jar** examples use the **`curl`** lines.

| Step | Check |
| ---- | ----- |
| 0 | Migrated, `APP_ENV=local` (or `app()->isLocal()` true), server **8011** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /_exercise/login` with `-c cj` — text mentions logged in + a **project id** |
| 3 | `GET /projects` with `-b cj` — **200** JSON list (your org’s projects) |
| 4 | `GET /projects/{id}` — **200** (use id from step 2 or `route:list` / DB) |
| 5 | `PATCH /projects/{id}` — e.g. `{"title":"Renamed"}` — **200** with updated project |
| 6 | `DELETE /projects/{id}` (optional) — **204**; list again to confirm |
| 7 | Without cookies — `GET /projects` → **302** to `/login` (or 401/redirect per middleware) |
| 8 | `php artisan test --filter=ProjectPolicyTest` — green |

**1 — Health**

In the browser, open [http://127.0.0.1:8011/exercise](http://127.0.0.1:8011/exercise). Expect **`ok`**.

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8011/exercise"
```

**2 — Local dev login (sets session; ignore body except project id if you need it)** — in the **browser**, open [http://127.0.0.1:8011/_exercise/login](http://127.0.0.1:8011/_exercise/login), then continue in the same tab (or a new tab to the same host).

*Cookie-jar (terminal) equivalent:*

```bash
curl -sS -c cj -b cj "http://127.0.0.1:8011/_exercise/login"
```

**3 — List projects (JSON, requires login)** — in the same browser **after** step 2, open [http://127.0.0.1:8011/projects](http://127.0.0.1:8011/projects).

*Optional (curl with `cj` from the terminal):*

```bash
curl -sS -b cj "http://127.0.0.1:8011/projects"
```

**Tip (optional):** set `ID` to the first project’s id so you are not hard-coding `1`:

```bash
ID=$(curl -sS -b cj "http://127.0.0.1:8011/projects" | python3 -c "import json,sys; a=json.load(sys.stdin); print(a[0]['id'] if a else '')")
```

**3b — Show one project (GET, browser)** — after you know a real id (from the list JSON or a seed), open [http://127.0.0.1:8011/projects/<id>](http://127.0.0.1:8011/projects/<id>) in the **browser** (same session as `/_exercise/login`).

**4 — Update title (PATCH — `curl` or API client; no HTML form in this sample)** (replace `1` with a real `id` from the list, or `curl …/projects/$ID` with `ID` from above)

```bash
curl -sS -b cj -X PATCH "http://127.0.0.1:8011/projects/1" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"title":"Renamed from README"}'
```

**5 — Routes (sanity check)**

```bash
cd ch11-exercise-build-role-system/laravel && php artisan route:list --path=projects
```

**6 — Policy tests**

```bash
cd ch11-exercise-build-role-system/laravel && php artisan test --filter=ProjectPolicyTest
```
