# Chapter 16 — Space Shooter (TypeScript + Phaser)

A full Vite + TypeScript space shooter — the course capstone for reading real codebases, extracting services, config-driven tuning, Vitest, and production builds. The whole chapter walks this repo; lesson 16.12 is the retrospective on what transfers to product work.

Based on [stuarttodd-dev/project-space-shooter](https://github.com/stuarttodd-dev/project-space-shooter). In this academy repo the runnable app lives at the **chapter root** (`src/`, not a separate `app/` folder).

## Quick start

From the **repository root**:

```bash
cd php-to-js/16-space-shooter
npm install
npm run dev
```

Open the URL Vite prints (usually `http://localhost:5173`). Node 18+ recommended.

## Scripts

| Command | Purpose |
|---------|---------|
| `npm run dev` | Local dev server with hot reload |
| `npm run build` | Typecheck + production bundle to `dist/` |
| `npm run preview` | Preview the production build |
| `npm test` | Vitest — pure game logic and services |
| `node main.js` | Lesson 16.12 retrospective checklist |

## Architecture map

| Laravel habit | This repo |
|---------------|-----------|
| Routes | Scene registration in `src/main.ts` |
| Controllers | Phaser scenes (`GameScene`, `StartScene`, …) |
| Models / entities | `src/entities/` |
| Config | `src/config.ts` |
| Services | `SpawnController.ts`, `CollisionService.ts` |
| Public assets | `public/assets/` |

```text
src/
  main.ts                 # Phaser bootstrap, scene list
  config.ts               # Speeds, damage, score constants
  scenes/                 # Loading, Start, Game, GameOver, cutscenes
  entities/               # Player, Bullet, enemies
  SpawnController.ts      # Enemy spawn timers, boss trigger
  CollisionService.ts     # All overlap / collision handlers
  utils/                  # Touch controls, sound, explosions
tests/                    # Vitest specs for logic and services
public/assets/            # Images and audio (served as static files)
```

## Lesson retrospective (16.12)

Run the sandbox checklist:

```bash
node main.js
```

Expected output:

```
read real TS repo transfers
extract service class transfers
memorise Phaser API game-only
```

Skills that transfer: navigating a TypeScript repo, extracting services, config tuning, Vitest on pure functions, `npm run build` before deploy. Phaser-specific APIs are the vehicle, not the destination.

## SpawnController (lesson 16.6)

Extracted from `GameScene` — owns spawn timers, wave counter, boss spawn, and the `boss` reference. `GameScene` creates it in `create()` and calls `update()` each frame.

## CollisionService (lesson 16.6)

Extracted from `GameScene` — owns every overlap handler (bullets, shields, ricochet, ship collisions). `GameScene` delegates to `collisionService.update()` after enemy updates.

## Adding a new enemy

1. Create a class in `src/entities/enemies/` extending `BaseEnemy`.
2. Preload the texture in `LoadingScene.ts`.
3. Add a spawn path in `SpawnController`.
4. Handle score branches in `CollisionService`.
5. Add a constant under `SCORE_CONFIG` in `config.ts`.
6. Export from `src/entities/enemies/index.ts`.

## Build and deploy

```bash
npm run build
```

Output lands in `dist/`. Deploy to any static host. Assets under `public/` are copied into the build — paths like `assets/images/player_bullet.png` must match what Phaser loads.

## Tests

```bash
npm test
```

Pure logic (damage, spawn thresholds, config) is tested without booting Phaser. Keep new rules testable in `tests/` when you extend the game.
