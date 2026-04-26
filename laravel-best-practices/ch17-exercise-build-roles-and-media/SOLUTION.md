# Chapter 17 — Exercise: Spatie permissions + medialibrary (checklist and notes)

**Course page:** [Build a practical package-powered feature](http://127.0.0.1:38080/learn/sections/chapter-17-spatie-packages/build-a-roles-and-media-feature)

The lesson is a capstone: Composer packages, model traits, policies, **permission cache** discipline, and tests. A full app cannot live meaningfully in the Academy repo; use this as a **completion checklist** and operational notes (UK English) while you build in a real project branch.

---

## 1. Packages and one-time setup

```bash
composer require spatie/laravel-permission spatie/laravel-medialibrary
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

Register `HasRoles` on your `User` (or `Authenticatable`) model. Add `InteractsWithMedia` and `registerMediaCollections()` on the model you attach files to (for example an `Article` with a `cover` collection, single file, `image` rules).

---

## 2. Matrix (on paper, then in code)

| Action | Who may do it |
| --- | --- |
| Create article | `editor`, `admin` (example) |
| Upload cover to own article | `editor`, `admin` |
| Delete someone else’s article | `admin` only |

**Policy** methods must run on the **server** for `store`, `update`, and `delete`. Hiding a button in Blade is not enough.

After role changes in a deploy, clear the permission cache the way the chapter teaches, for example in a seeder (dev only) or a documented post-deploy line:

```bash
php artisan permission:cache-reset
```

---

## 3. Test titles (example “fails if” lines)

| Test | Fails if… |
| --- | --- |
| `test_editor_can_store_article_with_cover` | A user with the right role cannot create the row and see media attached (use `Storage::fake()` for the disk you configure). |
| `test_viewer_cannot_update_foreign_article` | A low-privilege user gets **403** on the **named** `update` route for another user’s article. |
| `test_permission_cache_sees_new_role_after_reset` (optional) | You assign a new permission and the first request still denies until cache reset (or you prove your seeder calls `artisan` / cache clear in CI). |

---

## 4. “Done” (from the course, shortened)

- Clone, `composer install`, `cp .env.example .env`, `php artisan key:generate`, `migrate`, `db:seed`, log in as seeded users, allow and deny paths work **without** one-off tinker.
- In six months you can read which **disk** you used, which **collections** exist, and how you clear permission cache after role deploys.

---

## 5. Optional activity log (lesson 17.7)

`spatie/laravel-activitylog` is optional. If you add it, one **structured** `activity()->log('article published')` (or a `->causedBy($user)`) in the same service class the policy already approved keeps audits aligned with authorisation, not with Blade clicks.
