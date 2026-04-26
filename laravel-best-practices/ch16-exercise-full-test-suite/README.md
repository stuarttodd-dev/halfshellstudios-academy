# Chapter 16 — Exercise: full test / CI (reference write-up)

**Course page:** [exercise-full-test-suite](http://127.0.0.1:38080/learn/sections/chapter-16-testing-laravel/exercise-full-test-suite)

See **[SOLUTION.md](SOLUTION.md)** for a worked example in the hand-in style the course describes (test matrix, CI YAML ideas, smoke vs contract tests).

## Run the bundled app (optional)

From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch16-exercise-full-test-suite`** and port **8016**.

## How to test (lesson)

1. **Health:** `GET /exercise` → `ok`.
2. In **your** project or a fork: `php artisan test` with a CI config that matches the lesson; use this exercise’s SOLUTION as a checklist, not a drop-in for your host’s secrets.
