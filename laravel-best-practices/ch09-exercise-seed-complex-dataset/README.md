# Chapter 9 — Exercise: seed a complex demo dataset

**Course page:** [Build a multi-tenant demo with factories and seeders](https://laravel.learnio.dev/learn/sections/chapter-9-factories-seeders-transactions/exercise-seed-complex-dataset)

## Run the app

The exercise is the **seed** itself — always run **`php artisan db:seed --force`** after migrate.

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch09-exercise-seed-complex-dataset`**, port **8009**, and **seed**.

## What’s in the app

Under **`laravel/database/`** (seeders, factories) and related models/migrations: `RoleSeeder`, `DemoContentSeeder`, `DatabaseSeeder` orchestration, factories for `Organisation`, `Project`, `Task`, `Tag`, etc. Aligns with the lesson’s shape: idempotent role seed, `DB::transaction` where useful, volume constant for task counts.

## How to test

1. **Health:** `GET /exercise` → `ok`.
2. **Fresh seed:** `php artisan migrate:fresh --seed` (destructive) — should complete without duplicate key errors on re-run for idempotent parts (`firstOrCreate` on roles).
3. **DB inspection:** in Tinker, count `Organisation`, `Project`, `Task` per org; spot-check `organisation_user` pivot.
4. **Transaction behaviour:** force a failure mid-seeder in a branch copy to confirm the lesson’s transaction story (optional local experiment).
