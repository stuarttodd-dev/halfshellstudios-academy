# Chapter 7 — Exercise: blog relations + index query

**Course page:** [Build a small blog domain with practical Eloquent relationships](http://127.0.0.1:38080/learn/sections/chapter-7-relations/exercise-build-relational-data-model)

## Schema

The lesson uses `posts`, `tags`, `post_tag`, `comments`, and `users`. If you already have `posts` from chapter 6, add only the new tables (`tags`, `post_tag`, `comments`) and any missing columns. Foreign keys: `posts.user_id`, `comments.post_id`, unique `(post_id, tag_id)` on the pivot.

## Reference files

- Migrations in `files/database/migrations/` (numbered after your existing migrations).
- Models: `Post`, `Tag`, `Comment` with inverse relations.
- `PostController@index` with the eager-load + `withCount` query from the lesson.
- `database/factories/PostFactory.php` sketch for factories/seed (expand per your seeder plan).

## Tag sync (controller excerpt)

```php
$post->tags()->sync($validated['tag_ids'] ?? []);
```

## Checklist

- Tinker: load `$post->author`, `$post->tags`, `comments` count.
- `php artisan db:seed` (your demo seeder) then hit the index route: query count should stay flat when paginating (use Laravel Debugbar or `DB::listen`).
