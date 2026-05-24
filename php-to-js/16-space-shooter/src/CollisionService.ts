import Phaser from 'phaser';
import { BULLET_CONFIG, ENEMY_BULLET_CONFIG, SCORE_CONFIG, PLAYER_CONFIG } from './config';
import { Player } from './entities/Player';
import { Bullet } from './entities/Bullet';
import { BaseEnemy } from './entities/enemies/BaseEnemy';
import { GroundEnemy } from './entities/enemies/GroundEnemy';
import { SideEnemy } from './entities/enemies/SideEnemy';
import { BossEnemy } from './entities/enemies/BossEnemy';
import { createExplosion } from './utils/Explosion';
import { soundEffects } from './utils/SoundEffects';
import { scaleSize } from './utils/ResponsiveScale';
import { SpawnController } from './SpawnController';

interface CollisionCallbacks {
  addScore(points: number): void;
  removeEnemy(enemy: BaseEnemy, index: number): void;
  handlePlayerDeath(): void;
}

/**
 * CollisionService — registers and handles all collision/overlap logic for GameScene.
 *
 * Responsibilities:
 *  - Collision cooldown tracking (player-enemy, enemy-enemy)
 *  - Player bullet vs enemy/boss collisions
 *  - Enemy bullet vs player (including shield detection) and homing missile collisions
 *  - Enemy bullet friendly-fire vs other enemies and boss
 *  - Bullet-vs-bullet ricochet physics
 *  - Player-enemy ship collision with shield/hull damage
 *  - Enemy-enemy bounce collisions
 *  - Dramatic boss explosion visual
 *
 * Usage:
 *  const collisions = new CollisionService(this, player, enemies, spawnController, {
 *    addScore: pts => this.addScore(pts),
 *    removeEnemy: (e, i) => this.removeEnemy(e, i),
 *    handlePlayerDeath: () => this.handlePlayerDeath(),
 *  });
 *  // in update(): collisions.update(currentTime);
 */
export class CollisionService {
  private readonly collisionDamage: number = 50;
  private lastPlayerCollisionTime: Map<BaseEnemy, number> = new Map();
  private lastEnemyCollisionTime: Map<string, number> = new Map();

  constructor(
    private readonly scene: Phaser.Scene,
    private readonly player: Player,
    private readonly enemies: BaseEnemy[],
    private readonly spawnController: SpawnController,
    private readonly callbacks: CollisionCallbacks
  ) {}

  /**
   * Call once per frame from GameScene.update(), after updateEnemies().
   * Preserves the original update order: boss update → boss collisions → all other collisions.
   */
  update(currentTime: number): void {
    const boss = this.spawnController.boss;

    // Boss receives a second update here — matches original GameScene behaviour
    if (boss?.active) {
      boss.update(this.player, currentTime);
      this.checkBossCollisions();
    }

    this.checkEnemyBulletCollisions();
    this.checkEnemyBulletVsEnemyCollisions();
    this.checkPlayerBulletCollisions();
    this.checkBulletVsBulletCollisions();
    this.checkPlayerEnemyCollisions(currentTime);
    this.checkEnemyEnemyCollisions(currentTime);
  }

  // ─── Boss ─────────────────────────────────────────────────────────────────

  private checkBossCollisions(): void {
    const boss = this.spawnController.boss;
    if (!boss?.active) return;

    for (let i = this.player.bullets.length - 1; i >= 0; i--) {
      const bullet = this.player.bullets[i];
      if (!(this.scene.physics as Phaser.Physics.Arcade.ArcadePhysics).overlap(bullet, boss)) continue;

      const isDestroyed = boss.takeDamage(BULLET_CONFIG.damage);
      this.player.restoreShield(1);
      this.player.removeBullet(bullet);
      bullet.destroy();

      if (isDestroyed) {
        this.createDramaticBossExplosion(boss.x, boss.y);
        this.callbacks.addScore(SCORE_CONFIG.boss);

        const bossIndex = this.enemies.indexOf(boss);
        if (bossIndex > -1) {
          this.callbacks.removeEnemy(boss, bossIndex);
        }
        this.spawnController.boss = undefined;

        const victoryText = this.scene.add.text(
          this.scene.scale.width / 2,
          this.scene.scale.height / 2,
          'BOSS DEFEATED!',
          { fontSize: '48px', color: '#00ff00', fontFamily: 'Arial', fontStyle: 'bold' }
        );
        victoryText.setOrigin(0.5);

        this.scene.time.delayedCall(3000, () => {
          victoryText.destroy();
          this.spawnController.bossSpawned = false;
          this.spawnController.enemiesDefeated = 0;
        });
      }
      break;
    }
  }

  // ─── Enemy bullets vs player ──────────────────────────────────────────────

  private checkEnemyBulletCollisions(): void {
    const physics = this.scene.physics as Phaser.Physics.Arcade.ArcadePhysics;
    const playerHasShield = this.player.getShield() > 0;
    const shieldRadius = scaleSize(this.scene, PLAYER_CONFIG.shieldRadius);

    for (const enemy of this.enemies) {
      for (let i = enemy.bullets.length - 1; i >= 0; i--) {
        const bullet = enemy.bullets[i];
        if (!bullet.active) continue;

        let isColliding = false;

        if (playerHasShield) {
          const dx = this.player.x - bullet.x;
          const dy = this.player.y - bullet.y;
          const distance = Math.sqrt(dx * dx + dy * dy);
          const bulletBody = bullet.body as Phaser.Physics.Arcade.Body;
          const bulletRadius = bulletBody ? bulletBody.width / 2 : bullet.displayWidth / 2;
          isColliding = distance <= shieldRadius + bulletRadius;
        } else {
          isColliding = physics.overlap(bullet, this.player);
        }

        if (isColliding) {
          this.player.takeDamage(ENEMY_BULLET_CONFIG.damage);
          enemy.removeBullet(bullet);
          bullet.destroy();
        }
      }
    }

    // Boss homing missiles vs player
    const boss = this.spawnController.boss;
    if (boss?.active) {
      for (let i = boss.homingMissiles.length - 1; i >= 0; i--) {
        const missile = boss.homingMissiles[i];
        if (!missile?.active) continue;

        let isColliding = false;

        if (playerHasShield) {
          const dx = this.player.x - missile.x;
          const dy = this.player.y - missile.y;
          const distance = Math.sqrt(dx * dx + dy * dy);
          const missileBody = missile.body as Phaser.Physics.Arcade.Body;
          const missileRadius = missileBody ? missileBody.width / 2 : missile.displayWidth / 2;
          isColliding = distance <= shieldRadius + missileRadius;
        } else {
          isColliding = physics.overlap(missile, this.player);
        }

        if (isColliding) {
          this.player.takeDamage(ENEMY_BULLET_CONFIG.damage * 1.5);
          boss.homingMissiles.splice(i, 1);
          missile.destroy();
        }
      }
    }
  }

  // ─── Enemy bullet friendly-fire vs other enemies ──────────────────────────

  private checkEnemyBulletVsEnemyCollisions(): void {
    const physics = this.scene.physics as Phaser.Physics.Arcade.ArcadePhysics;

    for (let i = 0; i < this.enemies.length; i++) {
      const firingEnemy = this.enemies[i];
      if (!firingEnemy.active) continue;

      for (let bulletIdx = firingEnemy.bullets.length - 1; bulletIdx >= 0; bulletIdx--) {
        const bullet = firingEnemy.bullets[bulletIdx];
        if (!bullet.active) continue;

        for (let j = 0; j < this.enemies.length; j++) {
          const targetEnemy = this.enemies[j];
          if (!targetEnemy.active) continue;
          if (firingEnemy === targetEnemy) continue;

          if (!physics.overlap(bullet, targetEnemy)) continue;

          const enemyDestroyed = targetEnemy.takeDamage(ENEMY_BULLET_CONFIG.damage);
          createExplosion(
            this.scene,
            (bullet.x + targetEnemy.x) / 2,
            (bullet.y + targetEnemy.y) / 2,
            0.6
          );
          firingEnemy.removeBullet(bullet);
          bullet.destroy();

          if (enemyDestroyed) {
            createExplosion(this.scene, targetEnemy.x, targetEnemy.y, 1);
            this.spawnController.enemiesDefeated++;
            if (targetEnemy instanceof GroundEnemy) {
              this.callbacks.addScore(SCORE_CONFIG.groundEnemy);
            } else if (targetEnemy instanceof SideEnemy) {
              this.callbacks.addScore(SCORE_CONFIG.sideEnemy);
            } else if (targetEnemy instanceof BossEnemy) {
              this.callbacks.addScore(SCORE_CONFIG.boss);
            }
            const idx = this.enemies.indexOf(targetEnemy);
            if (idx > -1) this.callbacks.removeEnemy(targetEnemy, idx);
          }
          break;
        }
      }
    }

    // Boss homing missiles vs enemies
    const boss = this.spawnController.boss;
    if (boss?.active) {
      for (let missileIdx = boss.homingMissiles.length - 1; missileIdx >= 0; missileIdx--) {
        const missile = boss.homingMissiles[missileIdx];
        if (!missile?.active) continue;

        for (let j = 0; j < this.enemies.length; j++) {
          const targetEnemy = this.enemies[j];
          if (!targetEnemy.active) continue;

          if (!physics.overlap(missile, targetEnemy)) continue;

          const enemyDestroyed = targetEnemy.takeDamage(ENEMY_BULLET_CONFIG.damage * 1.5);
          createExplosion(
            this.scene,
            (missile.x + targetEnemy.x) / 2,
            (missile.y + targetEnemy.y) / 2,
            0.7
          );
          boss.homingMissiles.splice(missileIdx, 1);
          missile.destroy();

          if (enemyDestroyed) {
            createExplosion(this.scene, targetEnemy.x, targetEnemy.y, 1);
            this.spawnController.enemiesDefeated++;
            if (targetEnemy instanceof GroundEnemy) {
              this.callbacks.addScore(SCORE_CONFIG.groundEnemy);
            } else if (targetEnemy instanceof SideEnemy) {
              this.callbacks.addScore(SCORE_CONFIG.sideEnemy);
            }
            const idx = this.enemies.indexOf(targetEnemy);
            if (idx > -1) this.callbacks.removeEnemy(targetEnemy, idx);
          }
          break;
        }
      }
    }

    // Regular enemy bullets vs boss
    if (boss?.active) {
      for (const enemy of this.enemies) {
        if (!enemy.active) continue;

        for (let bulletIdx = enemy.bullets.length - 1; bulletIdx >= 0; bulletIdx--) {
          const bullet = enemy.bullets[bulletIdx];
          if (!bullet.active) continue;

          if (!physics.overlap(bullet, boss)) continue;

          const bossDestroyed = boss.takeDamage(ENEMY_BULLET_CONFIG.damage);
          createExplosion(
            this.scene,
            (bullet.x + boss.x) / 2,
            (bullet.y + boss.y) / 2,
            0.8
          );
          enemy.removeBullet(bullet);
          bullet.destroy();

          if (bossDestroyed) {
            createExplosion(this.scene, boss.x, boss.y, 2);
            this.spawnController.enemiesDefeated++;
            this.callbacks.addScore(SCORE_CONFIG.boss);
            boss.destroy();
            this.spawnController.boss = undefined;
          }
          break;
        }
      }
    }
  }

  // ─── Player bullets vs enemies ────────────────────────────────────────────

  private checkPlayerBulletCollisions(): void {
    const physics = this.scene.physics as Phaser.Physics.Arcade.ArcadePhysics;

    for (let i = this.player.bullets.length - 1; i >= 0; i--) {
      const bullet = this.player.bullets[i];
      for (const enemy of this.enemies) {
        if (physics.overlap(bullet, enemy)) {
          this.handlePlayerBulletHitEnemy(bullet, enemy);
          break;
        }
      }
    }
  }

  private handlePlayerBulletHitEnemy(bullet: Bullet, enemy: BaseEnemy): void {
    const isDestroyed = enemy.takeDamage(BULLET_CONFIG.damage);
    this.player.restoreShield(1);
    this.player.removeBullet(bullet);
    bullet.destroy();

    if (isDestroyed) {
      createExplosion(this.scene, enemy.x, enemy.y, 1);
      this.spawnController.enemiesDefeated++;
      if (enemy instanceof GroundEnemy) {
        this.callbacks.addScore(SCORE_CONFIG.groundEnemy);
      } else if (enemy instanceof SideEnemy) {
        this.callbacks.addScore(SCORE_CONFIG.sideEnemy);
      }
      const idx = this.enemies.indexOf(enemy);
      if (idx > -1) this.callbacks.removeEnemy(enemy, idx);
    }
  }

  // ─── Bullet vs bullet ricochet ────────────────────────────────────────────

  private checkBulletVsBulletCollisions(): void {
    const physics = this.scene.physics as Phaser.Physics.Arcade.ArcadePhysics;
    const boss = this.spawnController.boss;

    type BulletEntry = {
      bullet: Phaser.Physics.Arcade.Sprite;
      owner: 'player' | 'enemy' | 'boss';
      enemy?: BaseEnemy;
    };

    const allBullets: BulletEntry[] = [];

    if (this.player) {
      for (const bullet of this.player.bullets) {
        if (bullet.active) allBullets.push({ bullet, owner: 'player' });
      }
    }

    for (const enemy of this.enemies) {
      for (const bullet of enemy.bullets) {
        if (bullet.active) {
          allBullets.push({ bullet, owner: enemy instanceof BossEnemy ? 'boss' : 'enemy', enemy });
        }
      }
    }

    if (boss?.active) {
      for (const missile of boss.homingMissiles) {
        if (missile?.active) allBullets.push({ bullet: missile, owner: 'boss' });
      }
    }

    for (let i = 0; i < allBullets.length; i++) {
      const b1 = allBullets[i];
      if (!b1.bullet.active) continue;

      for (let j = i + 1; j < allBullets.length; j++) {
        const b2 = allBullets[j];
        if (!b2.bullet.active) continue;

        if (b1.owner === 'boss' && b2.owner === 'boss') {
          if (physics.overlap(b1.bullet, b2.bullet)) {
            createExplosion(
              this.scene,
              (b1.bullet.x + b2.bullet.x) / 2,
              (b1.bullet.y + b2.bullet.y) / 2,
              0.7
            );
            if (boss) {
              const idx1 = boss.homingMissiles.indexOf(b1.bullet as any);
              if (idx1 > -1) boss.homingMissiles.splice(idx1, 1);
              const idx2 = boss.homingMissiles.indexOf(b2.bullet as any);
              if (idx2 > -1) boss.homingMissiles.splice(idx2, 1);
            }
            b1.bullet.destroy();
            b2.bullet.destroy();
            this.callbacks.addScore(SCORE_CONFIG.bulletVsBullet);
          }
          continue;
        }

        if (!physics.overlap(b1.bullet, b2.bullet)) continue;

        createExplosion(
          this.scene,
          (b1.bullet.x + b2.bullet.x) / 2,
          (b1.bullet.y + b2.bullet.y) / 2,
          0.4
        );

        try {
          if (this.scene.cache.audio.exists('ricochet')) {
            this.scene.sound.play('ricochet', { volume: 0.075 });
          }
        } catch {
          // ignore sound errors
        }

        const body1 = b1.bullet.body as Phaser.Physics.Arcade.Body;
        const body2 = b2.bullet.body as Phaser.Physics.Arcade.Body;

        if (body1 && body2) {
          const vel1X = body1.velocity.x;
          const vel1Y = body1.velocity.y;
          const vel2X = body2.velocity.x;
          const vel2Y = body2.velocity.y;
          const relVelX = vel1X - vel2X;
          const relVelY = vel1Y - vel2Y;
          const dx = b1.bullet.x - b2.bullet.x;
          const dy = b1.bullet.y - b2.bullet.y;
          const dist = Math.sqrt(dx * dx + dy * dy);

          if (dist > 0) {
            const normX = dx / dist;
            const normY = dy / dist;
            const dot = relVelX * normX + relVelY * normY;
            const bounceFactor = 1.2;
            const nv1X = vel1X - 2 * dot * normX * bounceFactor;
            const nv1Y = vel1Y - 2 * dot * normY * bounceFactor;
            const nv2X = vel2X + 2 * dot * normX * bounceFactor;
            const nv2Y = vel2Y + 2 * dot * normY * bounceFactor;

            const speed1 = Math.sqrt(vel1X ** 2 + vel1Y ** 2);
            const speed2 = Math.sqrt(vel2X ** 2 + vel2Y ** 2);
            const newSpeed1 = Math.sqrt(nv1X ** 2 + nv1Y ** 2);
            const newSpeed2 = Math.sqrt(nv2X ** 2 + nv2Y ** 2);

            if (newSpeed1 > 0 && newSpeed2 > 0) {
              body1.setVelocity((nv1X / newSpeed1) * speed1, (nv1Y / newSpeed1) * speed1);
              body2.setVelocity((nv2X / newSpeed2) * speed2, (nv2Y / newSpeed2) * speed2);
              b1.bullet.setRotation(Math.atan2(nv1Y / newSpeed1, nv1X / newSpeed1) + Phaser.Math.DegToRad(90));
              b2.bullet.setRotation(Math.atan2(nv2Y / newSpeed2, nv2X / newSpeed2) + Phaser.Math.DegToRad(90));
            }
          }
        }

        if (b1.owner === 'player' || b2.owner === 'player') {
          this.callbacks.addScore(SCORE_CONFIG.bulletVsBullet);
        }
      }
    }
  }

  // ─── Player vs enemy ship collisions ─────────────────────────────────────

  private checkPlayerEnemyCollisions(currentTime: number): void {
    const physics = this.scene.physics as Phaser.Physics.Arcade.ArcadePhysics;
    const playerHasShield = this.player.getShield() > 0;
    const playerShieldRadius = scaleSize(this.scene, PLAYER_CONFIG.shieldRadius);

    for (const enemy of this.enemies) {
      if (!enemy.active) continue;

      let isColliding = false;

      if (playerHasShield) {
        const dx = this.player.x - enemy.x;
        const dy = this.player.y - enemy.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        let enemyRadius: number;
        if (enemy instanceof BossEnemy && enemy.getShield() > 0) {
          enemyRadius = scaleSize(this.scene, 50);
        } else {
          const enemyBody = enemy.body as Phaser.Physics.Arcade.Body;
          enemyRadius = enemyBody ? enemyBody.width / 2 : enemy.displayWidth / 2;
        }
        isColliding = distance <= playerShieldRadius + enemyRadius;
      } else {
        const dx = this.player.x - enemy.x;
        const dy = this.player.y - enemy.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        const playerRadius = this.player.displayWidth / 2;
        const enemyBody = enemy.body as Phaser.Physics.Arcade.Body;
        const enemyRadius = enemyBody ? enemyBody.width / 2 : enemy.displayWidth / 2;
        isColliding = distance <= playerRadius + enemyRadius;
        if (!isColliding) {
          isColliding = physics.overlap(this.player, enemy);
        }
      }

      if (!isColliding) continue;

      const lastCollision = this.lastPlayerCollisionTime.get(enemy) || 0;
      if (currentTime - lastCollision < 500) continue;
      this.lastPlayerCollisionTime.set(enemy, currentTime);

      if (playerHasShield) {
        this.player.takeDamage(5);
      } else {
        this.player.takeDamage(this.collisionDamage);
      }

      const enemyDestroyed = enemy.takeDamage(this.collisionDamage);
      createExplosion(
        this.scene,
        (this.player.x + enemy.x) / 2,
        (this.player.y + enemy.y) / 2,
        0.8
      );

      if (enemyDestroyed) {
        createExplosion(this.scene, enemy.x, enemy.y, 1);
        this.spawnController.enemiesDefeated++;
        if (enemy instanceof GroundEnemy) {
          this.callbacks.addScore(SCORE_CONFIG.groundEnemy);
        } else if (enemy instanceof SideEnemy) {
          this.callbacks.addScore(SCORE_CONFIG.sideEnemy);
        } else if (enemy instanceof BossEnemy) {
          this.callbacks.addScore(SCORE_CONFIG.boss);
        }
        const idx = this.enemies.indexOf(enemy);
        if (idx > -1) this.callbacks.removeEnemy(enemy, idx);
      }

      if (this.player.getHull() <= 0) {
        this.callbacks.handlePlayerDeath();
        return;
      }
    }
  }

  // ─── Enemy vs enemy collisions ────────────────────────────────────────────

  private checkEnemyEnemyCollisions(currentTime: number): void {
    const physics = this.scene.physics as Phaser.Physics.Arcade.ArcadePhysics;

    // Regular enemy pairs
    for (let i = 0; i < this.enemies.length; i++) {
      const enemy1 = this.enemies[i];
      if (!enemy1.active) continue;

      for (let j = i + 1; j < this.enemies.length; j++) {
        const enemy2 = this.enemies[j];
        if (!enemy2.active) continue;

        if (!physics.overlap(enemy1, enemy2)) continue;

        const key1 = `${enemy1.name || 'enemy'}_${enemy1.x}_${enemy1.y}_${i}`;
        const key2 = `${enemy2.name || 'enemy'}_${enemy2.x}_${enemy2.y}_${j}`;
        const collisionKey = [key1, key2].sort().join('-');

        const lastCollision = this.lastEnemyCollisionTime.get(collisionKey) || 0;
        if (currentTime - lastCollision < 500) continue;
        this.lastEnemyCollisionTime.set(collisionKey, currentTime);

        const dx = enemy1.x - enemy2.x;
        const dy = enemy1.y - enemy2.y;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance > 0) {
          const normX = dx / distance;
          const normY = dy / distance;
          const pushDistance = 20;
          const midX = (enemy1.x + enemy2.x) / 2;
          const midY = (enemy1.y + enemy2.y) / 2;
          const halfSum = (enemy1.displayWidth / 2 + enemy2.displayWidth / 2 + pushDistance) / 2;

          enemy1.x = midX + normX * halfSum;
          enemy1.y = midY + normY * halfSum;
          enemy2.x = midX - normX * halfSum;
          enemy2.y = midY - normY * halfSum;

          if (enemy1.body) {
            const body1 = enemy1.body as Phaser.Physics.Arcade.Body;
            body1.x = enemy1.x;
            body1.y = enemy1.y;
            body1.setVelocity(normX * 150 * 0.6, normY * 150 * 0.6);
          }
          if (enemy2.body) {
            const body2 = enemy2.body as Phaser.Physics.Arcade.Body;
            body2.x = enemy2.x;
            body2.y = enemy2.y;
            body2.setVelocity(-normX * 150 * 0.6, -normY * 150 * 0.6);
          }

          if (enemy1 instanceof BossEnemy && 'horizontalDirection' in enemy1) {
            (enemy1 as any).horizontalDirection *= -1;
          }
          if (enemy2 instanceof BossEnemy && 'horizontalDirection' in enemy2) {
            (enemy2 as any).horizontalDirection *= -1;
          }
        }
        break;
      }
    }

    // Enemies vs boss — bouncing off boss restores boss shields
    for (let i = this.enemies.length - 1; i >= 0; i--) {
      const potentialBoss = this.enemies[i];
      if (!potentialBoss.active || !(potentialBoss instanceof BossEnemy)) continue;

      const boss = potentialBoss as BossEnemy;

      for (let j = this.enemies.length - 1; j >= 0; j--) {
        const enemy = this.enemies[j];
        if (!enemy.active || enemy === boss || enemy instanceof BossEnemy) continue;

        if (!physics.overlap(enemy, boss)) continue;

        const key1 = `${enemy.name || 'enemy'}_${enemy.x}_${enemy.y}_${j}`;
        const key2 = `boss_${boss.x}_${boss.y}_${i}`;
        const collisionKey = [key1, key2].sort().join('-');

        const lastCollision = this.lastEnemyCollisionTime.get(collisionKey) || 0;
        if (currentTime - lastCollision < 500) continue;
        this.lastEnemyCollisionTime.set(collisionKey, currentTime);

        const oldShield = boss.getShield();
        boss.restoreShield(100);
        const newShield = boss.getShield();

        if (newShield > oldShield) {
          const powerUpText = this.scene.add.text(boss.x, boss.y - 60, 'SHIELD POWER UP!', {
            fontSize: '24px',
            color: '#00ffff',
            fontFamily: 'Arial',
            fontStyle: 'bold',
            stroke: '#000000',
            strokeThickness: 3,
          });
          powerUpText.setOrigin(0.5);
          powerUpText.setDepth(1000);
          this.scene.tweens.add({
            targets: powerUpText,
            y: powerUpText.y - 50,
            alpha: 0,
            duration: 1500,
            ease: 'Power2',
            onComplete: () => powerUpText.destroy(),
          });
        }

        const dx = enemy.x - boss.x;
        const dy = enemy.y - boss.y;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance > 0) {
          const normX = dx / distance;
          const normY = dy / distance;
          const pushDistance = 50;

          enemy.x = boss.x + normX * (boss.displayWidth / 2 + enemy.displayWidth / 2 + pushDistance);
          enemy.y = boss.y + normY * (boss.displayHeight / 2 + enemy.displayHeight / 2 + pushDistance);

          if (enemy.body) {
            const body = enemy.body as Phaser.Physics.Arcade.Body;
            body.x = enemy.x;
            body.y = enemy.y;
            body.setVelocity(normX * 200 * 0.8, normY * 200 * 0.8);
          }
        }
      }
    }
  }

  // ─── Visual effects ───────────────────────────────────────────────────────

  private createDramaticBossExplosion(x: number, y: number): void {
    soundEffects.playExplosion();

    const explosionLayers = 5;
    const baseSize = 50;

    for (let layer = 0; layer < explosionLayers; layer++) {
      const delay = layer * 150;
      const layerSize = baseSize + layer * 30;
      const particleCount = 20 + layer * 10;

      const ring = this.scene.add.circle(x, y, layerSize * 0.3, 0xffffff, 0);
      ring.setStrokeStyle(4 - layer, 0xff6600, 1);
      ring.setDepth(1000);
      this.scene.tweens.add({
        targets: ring,
        radius: layerSize * 3,
        alpha: 0,
        duration: 800,
        delay,
        ease: 'Power2',
        onComplete: () => ring.destroy(),
      });

      const colors = [0xffff00, 0xff6600, 0xff0000, 0xffffff, 0x00ffff];
      for (let i = 0; i < particleCount; i++) {
        const angle = (i / particleCount) * Math.PI * 2;
        const distance = Phaser.Math.Between(layerSize * 0.5, layerSize);
        const px = x + Math.cos(angle) * distance;
        const py = y + Math.sin(angle) * distance;
        const particle = this.scene.add.circle(px, py, Phaser.Math.Between(4, 8), colors[layer % colors.length], 1);
        particle.setDepth(1000);
        this.scene.tweens.add({
          targets: particle,
          alpha: 0,
          scale: 0,
          x: px + Math.cos(angle) * layerSize * 3,
          y: py + Math.sin(angle) * layerSize * 3,
          duration: 600 + layer * 100,
          delay,
          ease: 'Power2',
          onComplete: () => particle.destroy(),
        });
      }
    }

    const centralFlash = this.scene.add.circle(x, y, 20, 0xffffff, 1);
    centralFlash.setDepth(1001);
    this.scene.tweens.add({
      targets: centralFlash,
      radius: 300,
      alpha: 0,
      duration: 1000,
      ease: 'Power2',
      onComplete: () => centralFlash.destroy(),
    });

    const secondaryFlash = this.scene.add.circle(x, y, 10, 0x00ffff, 1);
    secondaryFlash.setDepth(1001);
    this.scene.tweens.add({
      targets: secondaryFlash,
      radius: 400,
      alpha: 0,
      duration: 1200,
      delay: 200,
      ease: 'Power2',
      onComplete: () => secondaryFlash.destroy(),
    });

    this.scene.cameras.main.shake(800, 0.02);

    this.scene.time.delayedCall(200, () => soundEffects.playExplosion());
    this.scene.time.delayedCall(400, () => soundEffects.playExplosion());
  }
}
