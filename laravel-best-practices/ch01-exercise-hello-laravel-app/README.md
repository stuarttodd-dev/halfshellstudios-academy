# Chapter 1 (Laravel tour) — Mini project: Hello Laravel app

**Course page:** [mini-project-hello-laravel-app](https://laravel.learnio.dev/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app)

**Prerequisites:** PHP 8.3+, Composer, SQLite (PDO) — [install path list](../README.md#prerequisites-install-once-on-your-machine). Run the commands below from `laravel-best-practices/`, not the monorepo root.

## Run the app

From `laravel-best-practices/`:

```bash
cd ch01-exercise-hello-laravel-app
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8001
```

## What’s in the app

The runnable solution is under **`laravel/`** — `routes/solution.php` with **`GET /hello`** and `resources/views/hello.blade.php`. `routes/web.php` loads the health routes and `solution.php`.

A parallel **`files/`** tree holds the same paths for quick reference.

### Lesson acceptance (map this to the course)

You can tick these off as “done” for the tour mini project (details in [SOLUTION.md](SOLUTION.md)):

- App boots with `php artisan serve` on the **8001** (or the port you choose — keep commands consistent).
- **`GET /exercise`** returns `ok` and **`GET /hello`** returns a Blade view (a short HTML greeting, not 500/404).
- You can name **`routes/*` + `web.php` wiring** and the flow from the lesson questions.

**If you get stuck:** `cd` to `ch01-…/laravel` and confirm `php artisan` runs; re-run the [Run the app](#run-the-app) block; ensure nothing else is bound to the same port.

---

## How to test everything

**Browser first (optional):** For **GET** routes you can open the same URLs in your browser. If the app has a **login** (or `/_exercise/login`), sign in in the browser and browse—`curl` is only needed for **POST / PUT / PATCH / DELETE**, JSON bodies, or when you want a copy-pastable one-liner. See [Browser vs curl](../README.md#browser-vs-curl).


**Port:** `8001` (all examples use `http://127.0.0.1:8001`).

Work through these in order. After [Run the app](#run-the-app), the server should be up and migrations applied.

| Step | What to do | What you should see |
| ---- | ---------- | ------------------- |
| 1 | Health route responds | `ok` plain text |
| 2 | Hello route responds | HTML with the Blade “Hello” view |
| 3 | Optional: `route:list` | `exercise` and `hello` registered as you expect |

**1 — Health**

```bash
curl -sS "http://127.0.0.1:8001/exercise"
```

**2 — Hello (Blade)**

```bash
curl -sS "http://127.0.0.1:8001/hello"
```

**3 — Route inventory (from `ch01-exercise-hello-laravel-app/laravel/`)**

```bash
cd ch01-exercise-hello-laravel-app/laravel && php artisan route:list
```

**4 — Outcomes in the code**

- `routes/web.php` — loads `solution.php` when present.
- `routes/solution.php` — `GET /hello` → `view('hello')`.
- `resources/views/hello.blade.php` — the greeting.

See [SOLUTION.md](SOLUTION.md) for the learning goals. General setup: [main README](../README.md).
