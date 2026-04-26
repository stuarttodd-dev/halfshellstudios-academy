# Chapter 17 — Exercise: Spatie roles + media (checklist)

**Course page:** [build-a-roles-and-media-feature](https://laravel.learnio.dev/learn/sections/chapter-17-spatie-packages/build-a-roles-and-media-feature)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — the **capstone** is usually done in a branch where you add Spatie packages; this folder’s `laravel/` is a **spine** only.

The capstone expects **package install**, migrations, and a **test matrix** in your app. See **[SOLUTION.md](SOLUTION.md)** for a checklist; this repository’s `laravel/` app is a **thin** scaffold (see `routes/solution.php`).

### Lesson acceptance (course)

- **Spatie permissions (or the role package the course names)** with **seeded** roles/permissions and at least one **authorisation** check in a feature test.
- **Media (or spatie/laravel-medialibrary)** with upload + a rule like “only image types” (match the hand-in) and DB evidence after upload.
- `php artisan test` and/or manual **browser** proof that an unauthorised user cannot do the **forbidden** action.

## Run the bundled app (optional)

From `laravel-best-practices/`:

```bash
cd ch17-exercise-build-roles-and-media
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8017
```


---

## How to test everything

> **Tip:** `http://127.0.0.1:…` links in this section are **Markdown** (click in your editor or on GitHub). **Curl** and other terminal steps use a fenced `bash` block per snippet—**select and copy the whole fence** in one go (all lines, including `\` line continuations).

**Browser (GETs):** If the bundled app is up, open **`/exercise`** and **`/chapter-17`** in the **browser**; `curl` is optional. You will spend most of your time in **SOLUTION.md**, tests, and the UI you build. [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8017`. The capstone expects **package integration** (Spatie permission + media library style), seeds where needed, and a **test matrix** in your solution. This repo’s `laravel/` is intentionally **thin** — follow **[SOLUTION.md](SOLUTION.md)** for the full checklist, then test **your** app where you add those packages.

| Step | Check |
| ---- | ----- |
| 0 | (Optional) [Run the bundled app](#run-the-bundled-app-optional) — **8017** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /chapter-17` — smoke response |
| 3 | In **SOLUTION.md**: `composer require` lines, `vendor:publish` + migrate order, and **test cases** for “cannot upload X” / “role Y can Z” |
| 4 | In your feature branch: `php artisan test` after wiring — green before you call the capstone done |
| 5 | Manual: upload a file in the UI you build, verify **storage** and **media** records (or Spatie’s tables) in SQLite |

**1 — Health** (if the app is running)

In the browser, open [http://127.0.0.1:8017/exercise](http://127.0.0.1:8017/exercise). Expect **`ok`**.

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8017/exercise"
```

**2 — Chapter pointer**

In the browser, open [http://127.0.0.1:8017/chapter-17](http://127.0.0.1:8017/chapter-17).

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8017/chapter-17"
```
