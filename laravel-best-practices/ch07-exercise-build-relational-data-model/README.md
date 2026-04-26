# Chapter 7 — Exercise: blog relations + index query

**Course page:** [Build a small blog domain with practical Eloquent relationships](https://laravel.learnio.dev/learn/sections/chapter-7-relations/exercise-build-relational-data-model)

**Prerequisites:** [Root README](../README.md#prerequisites-install-once-on-your-machine) — this app ships a **`UserFactory` only**; the README gives a **tinker** script to build `Post` + `Tag` + `Comment` so you are not depending on a non-existent `PostFactory`.

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

### Lesson acceptance (course)

- **Relations on models:** `Post` → author, tags, comments with correct cardinality (per lesson ERD / narrative).
- **Index view:** `PostController@index` avoids **N+1** for what you display (e.g. `with` / `withCount`) and paginates or lists in a way the course specifies. Use Debugbar or `DB::listen` in the course to **prove** query count vs a naive “load everything in the loop” version.

---

## How to test everything

**Browser (GETs):** **`/exercise`** and **`/posts`** are unauthenticated; open them in the **browser** (the post index is a small HTML fragment, not always a full document). [Browser vs curl](../README.md#browser-vs-curl).

**Port:** `8007`. After migrate, you may have **no** posts — still expect **200** HTML. Add posts via tinker or factories, then re-open `/posts` and watch **N+1** / query count in the lesson (Debugbar / `DB::listen`).

| Step | Check |
| ---- | ----- |
| 0 | Migrated, server **8007** |
| 1 | `/exercise` → `ok` |
| 2 | `GET /posts` — 200, Blade list (or empty) |
| 3 | Tinker: create posts with tags & comments, reload `/posts` |
| 4 | Eager loading / `withCount` in `PostController@index` per lesson |

**1 — Health**

In the browser, open **`http://127.0.0.1:8007/exercise`**. Expect **`ok`**.

*Optional (terminal):* `curl -sS "http://127.0.0.1:8007/exercise"`

**2 — Post index (this sample is a small `<ul>` fragment, not a full `<!DOCTYPE>` page)**

In the browser, open **`http://127.0.0.1:8007/posts`**. “View page source” if you need to see the list markup; or use a terminal snippet to peek: `curl -sS "http://127.0.0.1:8007/posts" | head -c 500`

**3 — Tinker: create a user, post, tag, attach, and comment, then re-open `/posts` in a browser** (one line; no `PostFactory` in this sample):

```bash
cd ch07-exercise-build-relational-data-model/laravel && php artisan tinker --execute='$u = \App\Models\User::factory()->create(); $tag = \App\Models\Tag::query()->create(["name" => "Laravel", "slug" => "laravel-".time()]); $p = \App\Models\Post::query()->create(["user_id" => $u->id, "title" => "Relational post", "slug" => "post-".time(), "body" => "Body", "published_at" => now()]); $p->tags()->attach($tag->id); \App\Models\Comment::query()->create(["post_id" => $p->id, "author_name" => "Sam", "content" => "Nice"]);'
```

**4 — Interactive tinker (optional):** `php artisan tinker` then e.g. `$p = \App\Models\Post::first(); $p?->author; $p?->tags; $p?->comments;`

**5 — Code**

- `app/Http/Controllers/PostController.php` — `with`, `withCount`, pagination.
- Models — `Post`, `Tag`, `Comment` relations.
