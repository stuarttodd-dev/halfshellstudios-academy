# Solution — Mini project: Hello Laravel app

**Course page:** [mini-project-hello-laravel-app](https://laravel.learnio.dev/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app)

## What “done” looks like

- A new Laravel app runs locally (`php artisan serve`).
- A dedicated **`GET /hello`** route returns a view with a short greeting.
- You can find the relevant route files and describe the flow, as the tour asks.

## Reference implementation in this folder

- `laravel/routes/solution.php` — `Route::get('/hello', …)` loading the `hello` view.
- `laravel/resources/views/hello.blade.php` — minimal HTML greeting.

The same two paths exist under `files/` for a compact read-only copy; from the chapter folder, `rsync -a files/ laravel/` keeps them in sync (see the [main README](../README.md#setup-one-chapter-app)).

**Course app wiring:** point the “solution” / code example link for this lesson at **`ch01-exercise-hello-laravel-app/`** (not `_laravel-skeleton`, which is only a template).
