import Phaser from 'phaser';
import { ENEMY_CONFIG } from './config';
import { BaseEnemy } from './entities/enemies/BaseEnemy';
import { GroundEnemy } from './entities/enemies/GroundEnemy';
import { SideEnemy } from './entities/enemies/SideEnemy';
import { BossEnemy } from './entities/enemies/BossEnemy';
import { scaleSize } from './utils/ResponsiveScale';

/**
 * SpawnController — manages all enemy spawning for GameScene.
 *
 * Responsibilities:
 *  - Timed enemy spawning (ground and side enemies)
 *  - Wave progression via enemiesDefeated counter
 *  - Boss spawn trigger and setup
 *  - Ownership of the active boss reference
 *
 * Usage:
 *  const spawn = new SpawnController(this, this.enemies);
 *  // in create(): spawn is ready to go
 *  // in update(): spawn.update(currentTime);
 *  // when an enemy dies in CollisionService: spawn.enemiesDefeated++
 */
export class SpawnController {
  public enemiesDefeated: number = 0;
  public bossSpawned: boolean = false;
  public boss: BossEnemy | undefined;

  private lastSpawnTime: number = 0;

  constructor(
    private readonly scene: Phaser.Scene,
    private readonly enemies: BaseEnemy[]
  ) {
    this.lastSpawnTime = scene.time.now;
  }

  /**
   * Call once per frame from GameScene.update().
   * Spawns regular enemies on interval and triggers boss when threshold is met.
   */
  update(currentTime: number): void {
    this.spawnEnemies(currentTime);
    if (!this.bossSpawned && this.enemiesDefeated >= 25) {
      this.spawnBoss();
    }
  }

  // ─── Private helpers ──────────────────────────────────────────────────────

  private spawnEnemies(currentTime: number): void {
    if (currentTime - this.lastSpawnTime < ENEMY_CONFIG.spawnInterval) {
      return;
    }
    this.lastSpawnTime = currentTime;

    const { width, height } = this.scene.scale;
    const DASHBOARD_HEIGHT = 200;
    const gameAreaHeight = height - DASHBOARD_HEIGHT;
    const enemyType = Phaser.Math.Between(0, 100);

    if (enemyType < 30) {
      // Ground enemy — spawns near bottom of game area
      const x = Phaser.Math.Between(50, width - 50);
      const spawnY = Math.min(ENEMY_CONFIG.groundSpawnY + 100, gameAreaHeight - 50);
      this.enemies.push(new GroundEnemy(this.scene, x, spawnY));
    } else {
      // Side enemies — spawn from left or right edge, below the boss patrol area
      const numSideEnemies = Phaser.Math.Between(1, 2);
      const stripHeight = scaleSize(this.scene, 40);
      const bossAreaBottom = stripHeight + 80 + 150;
      const minSpawnY = Math.max(100, bossAreaBottom);

      for (let i = 0; i < numSideEnemies; i++) {
        const fromLeft = Phaser.Math.Between(0, 1) === 0;
        const x = fromLeft ? -30 : width + 30;
        const y = Phaser.Math.Between(minSpawnY, gameAreaHeight - 100);
        this.enemies.push(new SideEnemy(this.scene, x, y, fromLeft));
      }
    }
  }

  private spawnBoss(): void {
    const { width } = this.scene.scale;
    const stripHeight = scaleSize(this.scene, 40);
    const topSpawnY = stripHeight + 80;
    this.spawnBossAt(width / 2, topSpawnY);
  }

  private spawnBossAt(x: number, y: number): void {
    if (this.bossSpawned) return;
    this.bossSpawned = true;

    try {
      if (this.scene.cache.audio.exists('danger')) {
        this.scene.sound.play('danger', { volume: 0.2 });
        console.log('🔊 Playing danger sound');
      } else {
        console.warn('⚠️ Danger sound not found in cache');
      }
    } catch (e) {
      console.error('Error playing danger sound:', e);
    }

    // Push any enemies away from the spawn point to avoid immediate collisions
    const spawnRadius = 100;
    for (let i = this.enemies.length - 1; i >= 0; i--) {
      const enemy = this.enemies[i];
      if (!enemy.active) continue;

      const dx = enemy.x - x;
      const dy = enemy.y - y;
      const distance = Math.sqrt(dx * dx + dy * dy);

      if (distance < spawnRadius) {
        const angle = Math.atan2(dy, dx);
        const pushDistance = spawnRadius + 50;
        enemy.x = x + Math.cos(angle) * pushDistance;
        enemy.y = y + Math.sin(angle) * pushDistance;
        if (enemy.body) {
          const body = enemy.body as Phaser.Physics.Arcade.Body;
          body.x = enemy.x;
          body.y = enemy.y;
        }
      }
    }

    this.boss = new BossEnemy(this.scene, x, y);
    this.enemies.push(this.boss);

    const { width } = this.scene.scale;
    const warningText = this.scene.add.text(
      width / 2,
      this.scene.scale.height / 2,
      'BOSS INCOMING!',
      { fontSize: '48px', color: '#ff0000', fontFamily: 'Arial', fontStyle: 'bold' }
    );
    warningText.setOrigin(0.5);
    this.scene.time.delayedCall(2000, () => warningText.destroy());
  }
}
