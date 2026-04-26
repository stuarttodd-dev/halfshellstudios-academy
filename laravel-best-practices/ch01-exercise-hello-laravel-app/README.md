# Chapter 1 (Laravel tour) — Mini project: Hello Laravel app

**Course page:** [mini-project-hello-laravel-app](https://laravel.learnio.dev/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app)

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

The runnable solution is under **`laravel/`** — including `routes/solution.php` with **`GET /hello`** and `resources/views/hello.blade.php`. `routes/web.php` loads the health routes and `solution.php`.

A parallel **`files/`** tree holds the same paths for quick reference. After **`rsync -a files/ laravel/`** (if you work from `files/`), the app matches; see the [main README](../README.md#runnable-app-in-each-chapter).

## How to test

1. **Health:** [http://127.0.0.1:8001/exercise](http://127.0.0.1:8001/exercise) should return the text `ok`.
2. **Hello page:** [http://127.0.0.1:8001/hello](http://127.0.0.1:8001/hello) should render the “Hello, Laravel” Blade view.
3. Compare with the lesson: you should be able to point a new developer at `routes/web.php` + `routes/solution.php` and explain the request lifecycle at a high level.

See [SOLUTION.md](SOLUTION.md) for the learning outcomes check. Global commands: [main README](../README.md).
