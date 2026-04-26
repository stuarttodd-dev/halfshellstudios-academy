# Chapter 6 — Exercise: `Post` model layer

**Course page:** [Build a complete Eloquent model layer for a blog post domain](https://laravel.learnio.dev/learn/sections/chapter-6-eloquent-models-migrations/exercise-build-model-layer)

## Run the app

From `laravel-best-practices/`:

```bash
cd ch06-exercise-build-model-layer
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8006
```


## What’s in the app

Under **`laravel/`**: `posts` migration (soft deletes, `user_id` FK), `Post` model with `fillable`, `casts`, `SoftDeletes`, `scopePublished`, and routes in `routes/solution.php` as needed for the lesson.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Migrate:** `php artisan migrate --force` (already run by setup).
3. **Tinker:** `php artisan tinker` — `Post::factory()->create([...]); Post::published()->count();` (adjust for your factory/attributes).
4. **Code review:** open `app/Models/Post.php` and confirm mass assignment and scopes match the lesson checklist.
