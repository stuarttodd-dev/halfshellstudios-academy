# Chapter 14 — Exercise: deploy (reference runbook)

**Course page:** [exercise-deploy-app](http://127.0.0.1:38080/learn/sections/chapter-14-vite-deploy/exercise-deploy-app)

The learning outcome is **operational** (runbook, evidence, rollback narrative), not a large amount of new PHP. See **[SOLUTION.md](SOLUTION.md)** for a completed runbook template.

## Run the bundled app (optional)

The `laravel/` folder is a minimal scaffold so you still have a working project if needed. From `laravel-best-practices/`, follow [Setup one chapter app](../README.md#setup-one-chapter-app) using folder **`ch14-exercise-deploy-app`** and port **8014**.

## How to test (lesson)

1. **Health:** `GET /exercise` → `ok`.
2. Follow **SOLUTION.md** / the course: document build, env, migrate, `queue:restart`, smoke checks, and rollback steps for *your* host — the repo cannot run your production for you.
