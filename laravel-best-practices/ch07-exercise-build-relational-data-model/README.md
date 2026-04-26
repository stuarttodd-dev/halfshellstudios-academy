# Chapter 7 — Exercise: blog relations + index query

**Course page:** [Build a small blog domain with practical Eloquent relationships](https://laravel.learnio.dev/learn/sections/chapter-7-relations/exercise-build-relational-data-model)

## Run the app

From `laravel-best-practices/`:

```bash
cd ch07-exercise-build-relational-data-model
[ -d files ] && rsync -a files/ laravel/
cd laravel
cp -n .env.example .env
composer install --no-interaction
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8007
```


## What’s in the app

Under **`laravel/`**: migrations for `posts`, `tags`, `post_tag`, `comments` (and related), models `Post`, `Tag`, `Comment` with relations, `PostController@index` with eager loads / `withCount` as in the lesson, and factory sketches under `database/factories/`.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Tinker:** load `$post->author`, `$post->tags`, comment counts; run `sync` on tags as in the lesson.
3. **Index route:** hit the blog index route defined in `routes/solution.php` — with `DB::listen` or Debugbar, confirm you are not N+1 querying when listing posts (lesson goal).
4. **Migrate + seed** (if you add seeders): `php artisan migrate` then seed demo data and paginate.
