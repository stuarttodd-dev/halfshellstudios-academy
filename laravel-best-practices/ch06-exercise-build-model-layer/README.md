# Chapter 6 — Exercise: `Post` model layer

**Course page:** [Build a complete Eloquent model layer for a blog post domain](https://laravel.learnio.dev/learn/sections/chapter-6-eloquent-models-migrations/exercise-build-model-layer)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — the `/posts-demo` check needs **migrated** tables; a row is not created until you use the tinker line below or your own seeder.

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

### Lesson acceptance (course)

- **Model API:** `fillable` (or explicit `$guarded` pattern from the course), `casts`, **`SoftDeletes`**, and at least one **`scope*`** the lesson names (e.g. `published()`).
- **Prove it:** `/posts-demo` `count` increases after you insert a `Post` (tinker in this README) or via your factory once you add one.

---

## How to test everything

> **Tip:** `http://127.0.0.1:…` links in this section are **Markdown** (click in your editor or on GitHub). **Curl** and other terminal steps use a fenced `bash` block per snippet—**select and copy the whole fence** in one go (all lines, including `\` line continuations).

**Browser (GETs):** Open **`/exercise`** and **`/posts-demo`** in the **browser** (JSON for the demo is fine in the tab). Tinker and artisan stay in the **terminal**. [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8006`.

| Step | Check |
| ---- | ----- |
| 0 | Migrated, server **8006** |
| 1 | `/exercise` → `ok` |
| 2 | `/posts-demo` returns JSON `count` (may be `0` until you add posts) |
| 3 | **Tinker / factory** — create `Post` rows, re-hit `/posts-demo` and see `count` increase |
| 4 | Model: `Post` fillable, casts, `scopePublished`, soft deletes (per your migration) |

**1 — Health**

In the browser, open [http://127.0.0.1:8006/exercise](http://127.0.0.1:8006/exercise). Expect **`ok`**.

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8006/exercise"
```

**2 — Demo route (JSON count)**

In the browser, open [http://127.0.0.1:8006/posts-demo](http://127.0.0.1:8006/posts-demo). The tab shows JSON with a `count` field.

*Optional — run in terminal:*

```bash
curl -sS "http://127.0.0.1:8006/posts-demo"
```

**3 — Create a post in tinker (from `laravel/`)** — this app does not ship a `PostFactory` by default, so create a `User` then a `Post` with a unique `slug`:

```bash
cd ch06-exercise-build-model-layer/laravel && php artisan tinker --execute='$u = \App\Models\User::factory()->create(); \App\Models\Post::query()->create(["user_id" => $u->id, "title" => "Demo", "slug" => "demo-".time(), "body" => "Hi", "is_published" => 1, "published_at" => now()]);'
```

Run step 2 again — `count` should be ≥ 1.

**4 — Read `app/Models/Post.php`** against the lesson checklist (mass assignment, scopes, `SoftDeletes`).
