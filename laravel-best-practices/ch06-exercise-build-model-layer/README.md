# Chapter 6 — Exercise: `Post` model layer

**Course page:** [Build a complete Eloquent model layer for a blog post domain](http://127.0.0.1:38080/learn/sections/chapter-6-eloquent-models-migrations/exercise-build-model-layer)

## Files

- Migration for `posts` with soft deletes and foreign key to `users.id` (ensure `users` exists first).
- `Post` model: `fillable`, `casts`, `SoftDeletes`, `scopePublished`.

## Apply

1. Copy migration and model.
2. `php artisan migrate`
3. Verify in Tinker: `Post::factory()->create([...]); Post::published()->count();`
4. Pair with Form Requests for create/update so you never pass `$request->all()` into `create`/`update`.
