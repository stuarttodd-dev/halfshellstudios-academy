# Chapter 11 — Exercise: org-scoped authorisation (policies)

**Course page:** [Build a B2B-style role and policy layer](https://laravel.learnio.dev/learn/sections/chapter-11-authorisation/exercise-build-role-system)

## Run the app

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch11-exercise-build-role-system`** and port **8011**.

## What’s in the app

Under **`laravel/`**: org + project migrations, `Organisation` / `Project` / `User` updates, `ProjectPolicy`, `ProjectController` with `$this->authorize`, `ProjectPolicyTest`, enums/routes as in the solution.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Automated:** `php artisan test --filter=ProjectPolicyTest` — cross-tenant 403/404, role matrix as you documented.
3. **Status codes:** confirm whether **cross-org** `show` is **403** or **404** in your policy and that tests match.
4. **Routes:** `php artisan route:list --path=projects` to see protected actions.
