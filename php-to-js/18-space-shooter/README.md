# Space Shooter — app

This is the Vite + TypeScript source for the space shooter game. It is the reference implementation for **Chapter 18** of the *PHP to JavaScript* course.

## Running the app

```bash
npm install
npm run dev
```

Open the URL printed by Vite (usually `http://localhost:5173`).

## Building

```bash
npm run build
```

Output lands in `dist/`. Deploy the folder to any static host (Netlify, Vercel, GitHub Pages, etc.).

## Running tests

```bash
npm test
```

Tests live in `tests/` and use [Vitest](https://vitest.dev/).

---

## Architecture overview

### Scenes

| File | Role |
|---|---|
| `LoadingScene.ts` | Preloads all images and audio before the game starts |
| `StartScene.ts` | Main menu — shows the title screen and starts the game |
| `IntroScene.ts` | Star Wars-style scroll crawl introducing the story |
| `GameScene.ts` | Main gameplay loop: player movement, UI, orchestrates services |
| `GameOverScene.ts` | End screen that displays the final score |
| `Episode2Scene.ts` | Post-game cutscene / episode progression |
| `cutscenes/` | Reusable Scene-based cutscene system (`BaseCutscene`, `Cutscene1Scene`) |

### Entities

| File | Role |
|---|---|
| `Player.ts` | Player ship: movement, shooting, shield, hull |
| `Bullet.ts` | Player bullet projectile |
| `enemies/BaseEnemy.ts` | Abstract base — shared health, bullets, `takeDamage` |
| `enemies/GroundEnemy.ts` | Ground-based enemy that patrols horizontally |
| `enemies/SideEnemy.ts` | Enters from the left or right edge |
| `enemies/BossEnemy.ts` | Boss with shields, patrol behaviour, homing missiles |
| `enemies/EnemyBullet.ts` | Projectile fired by ground/side enemies |
| `enemies/HomingMissile.ts` | Boss missile that tracks the player |

### Utilities

| File | Role |
|---|---|
| `SpawnController.ts` | Enemy spawn timers, wave counter, boss spawn trigger |
| `CollisionService.ts` | Every collision/overlap handler |
| `utils/Explosion.ts` | Particle-based explosion helper |
| `utils/SoundEffects.ts` | Centralised sound-effect wrappers |
| `utils/TouchControls.ts` | On-screen joystick and fire button for mobile |
| `utils/ResponsiveScale.ts` | Scale a pixel value relative to the canvas size |
| `utils/ScoreManager.ts` | Loads and saves the high-score JSON file |
| `config.ts` | All game constants (speeds, damage values, scene keys, …) |

---

## SpawnController (Chapter 18.11)

`SpawnController` was extracted from `GameScene` to give enemy spawning a single home. Before the refactor, `GameScene.create()` set up spawn timers inline and `GameScene` held the `boss` reference and `enemiesDefeated` counter directly. After the refactor, `GameScene` simply creates a `SpawnController` and calls `update()` on it each frame.

**What it owns:**
- Timed enemy spawning (ground enemies and side enemies on configurable intervals)
- Wave progression via `enemiesDefeated`
- Boss-spawn trigger (fires when `enemiesDefeated >= 25`)
- The `boss` reference and `bossSpawned` flag

**Class signature:**

```typescript
export class SpawnController {
  public enemiesDefeated: number;
  public bossSpawned: boolean;
  public boss: BossEnemy | undefined;

  constructor(
    private readonly scene: Phaser.Scene,
    private readonly enemies: BaseEnemy[]
  )

  /** Call once per frame from GameScene.update(). */
  update(currentTime: number): void
}
```

**Usage in GameScene:**

```typescript
// create()
this.spawnController = new SpawnController(this, this.enemies);

// update()
this.spawnController.update(currentTime);
```

---

## CollisionService (Chapter 18.12)

`CollisionService` was extracted from `GameScene` to collect every collision handler in one place. Before the refactor, `GameScene` was littered with nested loops and `physics.overlap` calls. After the refactor, `GameScene` creates a `CollisionService` in `create()` and delegates to `collisionService.update()` each frame.

**What it owns:**
- Player bullets vs enemies / boss
- Enemy bullets vs player (with shield-radius detection)
- Boss homing missiles vs player
- Enemy bullet friendly-fire vs other enemies and the boss
- Bullet-vs-bullet ricochet physics
- Player–enemy ship collisions (with per-pair cooldown)
- Enemy–enemy bounce collisions (enemies bouncing off the boss restore its shields)
- Dramatic multi-layer boss explosion visual

**Class signature:**

```typescript
export class CollisionService {
  constructor(
    private readonly scene: Phaser.Scene,
    private readonly player: Player,
    private readonly enemies: BaseEnemy[],
    private readonly spawnController: SpawnController,
    private readonly callbacks: {
      addScore(points: number): void;
      removeEnemy(enemy: BaseEnemy, index: number): void;
      handlePlayerDeath(): void;
    }
  )

  /** Call once per frame from GameScene.update(), after updateEnemies(). */
  update(currentTime: number): void
}
```

**Usage in GameScene:**

```typescript
// create()
this.collisionService = new CollisionService(
  this,
  this.player,
  this.enemies,
  this.spawnController,
  {
    addScore: (pts) => this.addScore(pts),
    removeEnemy: (enemy, idx) => this.removeEnemy(enemy, idx),
    handlePlayerDeath: () => this.handlePlayerDeath(),
  }
);

// update()
this.collisionService.update(currentTime);
```

---

## How to add a new enemy type

1. **Create the class** in `src/entities/enemies/` extending `BaseEnemy`.
   - Call `super(scene, x, y, 'your-texture-key')` in the constructor.
   - Implement `update(player, currentTime)` for movement and shooting logic.

2. **Register the texture** — preload the image in `LoadingScene.ts`:
   ```typescript
   this.load.image('your-texture-key', 'assets/images/your_enemy.png');
   ```

3. **Add a spawn path in `SpawnController`** — inside `spawnEnemies()`, adjust the random-range thresholds and push a new instance onto `this.enemies`:
   ```typescript
   this.enemies.push(new YourEnemy(this.scene, x, y));
   ```

4. **Handle collisions in `CollisionService`** — add an `instanceof YourEnemy` branch to `handlePlayerBulletHitEnemy` (and any other relevant handlers) to award the correct score:
   ```typescript
   } else if (enemy instanceof YourEnemy) {
     this.callbacks.addScore(SCORE_CONFIG.yourEnemy);
   }
   ```

5. **Add a score constant** in `config.ts` under `SCORE_CONFIG`.

6. **Export the new class** from `src/entities/enemies/index.ts`.
