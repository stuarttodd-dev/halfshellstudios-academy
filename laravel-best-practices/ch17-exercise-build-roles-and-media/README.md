# Chapter 17 — Exercise: Spatie roles + media (checklist)

**Course page:** [build-a-roles-and-media-feature](https://laravel.learnio.dev/learn/sections/chapter-17-spatie-packages/build-a-roles-and-media-feature)

The capstone expects **package install**, migrations, and a **test matrix** in your app. See **[SOLUTION.md](SOLUTION.md)** for a checklist; this repository’s `laravel/` app is a **thin** scaffold (see `routes/solution.php`).

## Run the bundled app (optional)

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch17-exercise-build-roles-and-media`** and port **8017**.

## How to test (lesson)

1. **Health:** `GET /exercise` → `ok` (or JSON pointer from the minimal route, depending on version).
2. In **your** Spatie-powered app: follow SOLUTION for `composer require`, `vendor:publish`, role/permission tables, media collections, and browser/feature tests for allow/deny paths.
