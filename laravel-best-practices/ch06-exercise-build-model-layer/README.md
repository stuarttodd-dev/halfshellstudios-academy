# Chapter 6 — Exercise: `Post` model layer

**Course page:** [Build a complete Eloquent model layer for a blog post domain](http://127.0.0.1:38080/learn/sections/chapter-6-eloquent-models-migrations/exercise-build-model-layer)

## Run the app

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch06-exercise-build-model-layer`** and port **8006**.

## What’s in the app

Under **`laravel/`**: `posts` migration (soft deletes, `user_id` FK), `Post` model with `fillable`, `casts`, `SoftDeletes`, `scopePublished`, and routes in `routes/solution.php` as needed for the lesson.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Migrate:** `php artisan migrate --force` (already run by setup).
3. **Tinker:** `php artisan tinker` — `Post::factory()->create([...]); Post::published()->count();` (adjust for your factory/attributes).
4. **Code review:** open `app/Models/Post.php` and confirm mass assignment and scopes match the lesson checklist.
