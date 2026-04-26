# Solution — Mini project: Hello Laravel app

**Course page:** [mini-project-hello-laravel-app](http://127.0.0.1:38080/learn/sections/chapter-laravel-tour/mini-project-hello-laravel-app)

## What “done” looks like

- A new Laravel app runs locally (`php artisan serve`).
- A dedicated **`GET /hello`** route returns a view (e.g. a Blade template) with a short greeting — not only the default welcome page.
- You can find `routes/web.php` (or the split route files you introduced) and explain what each part does at a high level, as the tour asks.

## Reference implementation in this folder

- `files/routes/solution.php` — `Route::get('/hello', …)` loading `hello` view.
- `files/resources/views/hello.blade.php` — minimal HTML greeting.

In the **materialised** app under `laravel/`, `routes/web.php` includes `routes/solution.php` after the `/` and `/exercise` health routes.

**Wire in the course app:** point the “solution” / code example link for this lesson at **`ch01-exercise-hello-laravel-app/`** (or `ch01-exercise-hello-laravel-app/laravel/`), not at **`_laravel-skeleton`**, which is the unmaintained-as-a-lesson **template** for all `chNN-exercise-*/laravel/` builds.
