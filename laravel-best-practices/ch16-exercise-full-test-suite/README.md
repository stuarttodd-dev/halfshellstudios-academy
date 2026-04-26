# Chapter 16 — Exercise: full test / CI (reference write-up)

**Course page:** [exercise-full-test-suite](https://laravel.learnio.dev/learn/sections/chapter-16-testing-laravel/exercise-full-test-suite)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — you need `composer install` in **`ch16-…/laravel`** before `php artisan test` reflects anything meaningful.

See **[SOLUTION.md](SOLUTION.md)** for a worked example in the hand-in style the course describes (test matrix, CI YAML ideas, smoke vs contract tests).

### Lesson acceptance (course)

- A **test matrix** (smoke, feature, unit — names per course) and what each layer **protects you from**.
- A **CI sketch** (GitHub Actions / GitLab / other) with **PHP + Composer** install, **migrate** for tests, and **parallel** vs **SQLite in-memory** cautions as your lesson says.
- `php artisan test` **passes** on the `laravel/` app in this repo (baseline); your hand-in extends that to **your** app.

## Run the bundled app (optional)

From `laravel-best-practices/`:

```bash
cd ch16-exercise-full-test-suite
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8016
```


---

## How to test everything

**Browser first (optional):** For **GET** routes you can open the same URLs in your browser. If the app has a **login** (or `/_exercise/login`), sign in in the browser and browse—`curl` is only needed for **POST / PUT / PATCH / DELETE**, JSON bodies, or when you want a copy-pastable one-liner. See [Browser vs curl](../README.md#browser-vs-curl).


**Port:** `8016`. The course asks for a **test strategy + CI** narrative; use **[SOLUTION.md](SOLUTION.md)** for the hand-in. The `laravel/` app still gives you a **concrete** `php artisan test` pass/fail.

| Step | Check |
| ---- | ----- |
| 0 | (Optional) [Run the bundled app](#run-the-bundled-app-optional) — **8016** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /chapter-16` — smoke |
| 3 | **Full test run** in `laravel/` (Feature + Unit as present) — expect exit code **0** |
| 4 | In **SOLUTION.md**: your **test pyramid** (unit vs feature), **what runs in CI** vs local-only, and a sample **CI YAML** or bullet list of jobs |
| 5 | (Optional) `php artisan test --parallel` if your app supports it — note any SQLite caveats from the course |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8016/exercise"
```

**2 — Chapter pointer**

```bash
curl -sS "http://127.0.0.1:8016/chapter-16"
```

**3 — Test suite**

```bash
cd ch16-exercise-full-test-suite/laravel && php artisan test
```
