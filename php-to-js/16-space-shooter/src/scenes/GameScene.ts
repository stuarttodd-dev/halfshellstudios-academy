import Phaser from 'phaser';
import { SCENE_KEYS, PLAYER_CONFIG, SCORE_CONFIG } from '../config';
import { Player } from '../entities/Player';
import { BaseEnemy } from '../entities/enemies/BaseEnemy';
import { BossEnemy } from '../entities/enemies/BossEnemy';
import { GroundEnemy } from '../entities/enemies/GroundEnemy';
import { SideEnemy } from '../entities/enemies/SideEnemy';
import { createExplosion } from '../utils/Explosion';
import { soundEffects } from '../utils/SoundEffects';
import { TouchControls } from '../utils/TouchControls';
import { scaleSize } from '../utils/ResponsiveScale';
import { SpawnController } from '../SpawnController';
import { CollisionService } from '../CollisionService';

/**
 * GameScene - The main game scene where gameplay happens
 *
 * Key concepts:
 * - create() runs once to set up the game
 * - update() runs every frame (60 times per second) - perfect for movement
 * - This is where the player, enemies, and bullets live
 *
 * Chapter 18 refactor:
 * - SpawnController owns all enemy spawning and wave progression
 * - CollisionService owns all collision detection and handlers
 * - GameScene focuses on player movement, UI, and scene transitions
 */
export class GameScene extends Phaser.Scene {
  private player?: Player;
  private background?: Phaser.GameObjects.Image | Phaser.GameObjects.Rectangle;
  private scoreText?: Phaser.GameObjects.Text;
  private enemies: BaseEnemy[] = [];
  private score: number = 0;
  private isGameOver: boolean = false;
  private hasWarpedOut: boolean = false;
  private levelCompleting: boolean = false;
  private backgroundMusic?: Phaser.Sound.BaseSound;
  private touchControls?: TouchControls;
  private playerBounds?: {
    minX: number;
    maxX: number;
    minY: number;
    maxY: number;
    effectiveHalfWidth: number;
    effectiveHalfHeight: number;
    borderLeft: number;
    borderRight: number;
    borderTop: number;
    borderBottom: number;
  };

  // Extracted service classes (Chapter 18)
  private spawnController!: SpawnController;
  private collisionService!: CollisionService;

  constructor() {
    super({ key: SCENE_KEYS.GAME });
  }

  /**
   * Called once when the scene is created
   * This sets up all the game objects
   */
  create() {
    const { width, height } = this.scale;

    const DASHBOARD_HEIGHT = 200;
    const gameAreaHeight = height - DASHBOARD_HEIGHT;

    // Reset game state
    this.score = 0;
    this.isGameOver = false;
    this.hasWarpedOut = false;
    this.levelCompleting = false;
    this.enemies = [];

    // Destroy any existing player bullets and player
    if (this.player) {
      this.player.bullets.forEach(bullet => bullet.destroy());
      this.player.bullets = [];
      this.player.destroy();
    }

    // Background
    if (this.textures.exists('gameBackground')) {
      const bg = this.add.image(width / 2, gameAreaHeight / 2, 'gameBackground');
      bg.setDisplaySize(width, gameAreaHeight);
      bg.setDepth(-1);
      this.background = bg;
      console.log('✅ Game background image displayed');
    } else {
      this.background = this.add.rectangle(width / 2, gameAreaHeight / 2, width, gameAreaHeight, 0x000011);
      this.background.setDepth(-1);
      console.log('⚠️ Using fallback background color');
    }

    // Black strip at top for UI visibility
    const stripHeight = scaleSize(this, 40);
    console.log('Top strip height:', stripHeight);
    const blackStrip = this.add.rectangle(width / 2, stripHeight / 2, width, stripHeight, 0x000000, 0.8);
    blackStrip.setDepth(0);

    // Separator line between game area and dashboard
    const separator = this.add.line(0, gameAreaHeight, width, gameAreaHeight, 0, gameAreaHeight, 0x00ff00, 0.5);
    separator.setDepth(99);

    // Create player
    this.player = new Player(this, PLAYER_CONFIG.startX, PLAYER_CONFIG.startY);

    // Calculate player boundary
    const playerDisplayWidth = this.player.displayWidth;
    const playerDisplayHeight = this.player.displayHeight;
    const hasShield = this.player.getShield() > 0;
    let effectiveHalfWidth: number;
    let effectiveHalfHeight: number;

    if (hasShield) {
      const scaledShieldRadius = scaleSize(this, PLAYER_CONFIG.shieldRadius);
      effectiveHalfWidth = scaledShieldRadius;
      effectiveHalfHeight = scaledShieldRadius;
      console.log('Shield active - boundary calculation:', {
        shieldRadius: PLAYER_CONFIG.shieldRadius,
        scaledShieldRadius: scaledShieldRadius.toFixed(2),
        effectiveHalfWidth: effectiveHalfWidth.toFixed(2),
        effectiveHalfHeight: effectiveHalfHeight.toFixed(2),
      });
    } else {
      effectiveHalfWidth = playerDisplayWidth / 2;
      effectiveHalfHeight = playerDisplayHeight / 2;
      console.log('No shield - boundary calculation:', {
        playerDisplayWidth: playerDisplayWidth.toFixed(2),
        playerDisplayHeight: playerDisplayHeight.toFixed(2),
        effectiveHalfWidth: effectiveHalfWidth.toFixed(2),
        effectiveHalfHeight: effectiveHalfHeight.toFixed(2),
      });
    }

    const minCenterX = -40 + effectiveHalfWidth;
    const maxCenterX = width - effectiveHalfWidth;
    const minCenterY = 0 + effectiveHalfHeight;
    const maxCenterY = gameAreaHeight - effectiveHalfHeight;

    this.playerBounds = {
      minX: minCenterX,
      maxX: maxCenterX,
      minY: minCenterY,
      maxY: maxCenterY,
      effectiveHalfWidth,
      effectiveHalfHeight,
      borderLeft: -40,
      borderRight: width,
      borderTop: 0,
      borderBottom: gameAreaHeight,
    };

    this.physics.world.setBounds(minCenterX, minCenterY, maxCenterX - minCenterX, maxCenterY - minCenterY);

    this.playerWarpIn();

    // Touch controls
    this.touchControls = new TouchControls(this);
    this.touchControls.create();

    // Score display
    const baseScoreFontSize = 24;
    const responsiveScoreFontSize = `${Math.round(scaleSize(this, baseScoreFontSize))}px`;
    const scoreStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: responsiveScoreFontSize,
      color: '#ffff00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
    };
    this.scoreText = this.add.text(width - scaleSize(this, 10), scaleSize(this, 10), 'Humans Evacuated: 0', scoreStyle);
    this.scoreText.setOrigin(1, 0);

    // Instantiate service classes (Chapter 18 extractions)
    this.spawnController = new SpawnController(this, this.enemies);
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

    // Sound setup
    soundEffects.setScene(this);
    if (this.sound && this.sound.locked) {
      this.sound.unlock();
    }

    try {
      if (this.cache.audio.exists('gamescene')) {
        const startMusic = this.sound.get('background');
        if (startMusic && startMusic.isPlaying) {
          startMusic.stop();
          console.log('🛑 Start scene background music stopped');
        }
        const existingGameMusic = this.sound.get('gamescene');
        if (existingGameMusic && existingGameMusic.isPlaying) {
          existingGameMusic.stop();
        }
        this.backgroundMusic = this.sound.add('gamescene', { loop: true, volume: 0.5 });
        this.backgroundMusic.play();
        console.log('✅ Game scene background music started (gamescene.mp3)');
      }
    } catch (e) {
      console.error('Error playing background music:', e);
    }
  }

  /**
   * Called when the scene is shut down/stopped
   */
  shutdown(): void {
    if (this.backgroundMusic && this.backgroundMusic.isPlaying) {
      this.backgroundMusic.stop();
      console.log('🛑 Game scene background music stopped');
    }
  }

  /**
   * Called every frame (60 times per second)
   */
  update() {
    if (this.isGameOver) return;

    const currentTime = this.time.now;

    // Level complete check
    if (this.score >= 100000 && !this.hasWarpedOut && !this.levelCompleting && this.player) {
      this.hasWarpedOut = true;
      this.levelCompleting = true;
      this.startLevelCompleteSequence();
      return;
    }

    // Player death check
    if (this.player && this.player.getHull() <= 0 && !this.isGameOver) {
      this.handlePlayerDeath();
      return;
    }

    // Player movement, shooting and UI
    if (this.player) {
      if (this.touchControls && this.player.cursors) {
        const cursors = this.player.cursors;
        const isKeyboardInput = cursors.up?.isDown || cursors.down?.isDown;

        if (isKeyboardInput) {
          let speedChange = 0;
          if (cursors.up?.isDown) {
            speedChange = 0.2;
          } else if (cursors.down?.isDown) {
            speedChange = -0.2;
          }
          if (speedChange !== 0) {
            const currentSpeedDial = this.touchControls.getInputState().speed || 0;
            const newSpeed = Phaser.Math.Clamp(currentSpeedDial + speedChange, -5, 5);
            this.touchControls.setSpeedValue(newSpeed);
          }
        } else {
          if (!this.touchControls.isSpeedDialActive()) {
            const currentSpeed = this.player.getCurrentSpeed();
            this.touchControls.setSpeed(currentSpeed);
          }
        }
      }

      if (this.touchControls) {
        this.player.touchInput = this.touchControls.getInputState();
      }

      this.player.update();

      // Boundary bouncing
      if (this.playerBounds && this.player.body) {
        const body = this.player.body as Phaser.Physics.Arcade.Body;
        let centerX = body.x;
        let centerY = body.y;
        let bounced = false;

        if (centerX < this.playerBounds.minX) {
          centerX = this.playerBounds.minX;
          body.setVelocityX(-body.velocity.x * 0.8);
          bounced = true;
        } else if (centerX > this.playerBounds.maxX) {
          centerX = this.playerBounds.maxX;
          body.setVelocityX(-body.velocity.x * 0.8);
          bounced = true;
        }
        if (centerY < this.playerBounds.minY) {
          centerY = this.playerBounds.minY;
          body.setVelocityY(-body.velocity.y * 0.8);
          bounced = true;
        } else if (centerY > this.playerBounds.maxY) {
          centerY = this.playerBounds.maxY;
          body.setVelocityY(-body.velocity.y * 0.8);
          bounced = true;
        }
        if (bounced) {
          this.player.setPosition(centerX, centerY);
        }
      }

      // Joystick visual sync
      if (this.touchControls) {
        const hasJoystickInput =
          this.player.touchInput?.joystickX !== 0 || this.player.touchInput?.joystickY !== 0;
        if (!hasJoystickInput) {
          this.touchControls.setJoystickFromAngle(this.player.angle);
        }
      }

      // Shooting
      if (this.player.isShooting()) {
        const bullet = this.player.shoot(this, currentTime);
        void bullet;
        if (this.touchControls) {
          this.touchControls.flashFireButton();
        }
      }

      this.updateBullets();
      this.updateUI();
    }

    // Delegate spawn and collision work to the extracted service classes
    this.spawnController.update(currentTime);
    this.updateEnemies(currentTime);
    this.collisionService.update(currentTime);
  }

  // ─── UI and scoring ───────────────────────────────────────────────────────

  private updateUI(): void {
    if (this.scoreText) {
      this.scoreText.setText(`Humans Evacuated: ${this.score.toLocaleString()}`);
    }
  }

  private addScore(points: number): void {
    this.score += points;
    this.updateUI();
  }

  getScore(): number {
    return this.score;
  }

  // ─── Bullet management ────────────────────────────────────────────────────

  private updateBullets(): void {
    if (!this.player) return;
    for (let i = this.player.bullets.length - 1; i >= 0; i--) {
      const bullet = this.player.bullets[i];
      if (bullet.isOffScreen()) {
        this.player.removeBullet(bullet);
        bullet.destroy();
      }
    }
  }

  // ─── Enemy lifecycle ──────────────────────────────────────────────────────

  /**
   * Updates all enemies and removes ones that are off-screen or inactive.
   */
  private updateEnemies(currentTime: number): void {
    if (!this.player) return;

    for (let i = this.enemies.length - 1; i >= 0; i--) {
      const enemy = this.enemies[i];
      if (!enemy || !enemy.active) continue;

      enemy.update(this.player, currentTime);

      // Constrain boss to game area
      if (enemy instanceof BossEnemy) {
        const DASHBOARD_HEIGHT = 200;
        const gameAreaHeight = this.scale.height - DASHBOARD_HEIGHT;
        if (enemy.y > gameAreaHeight - enemy.height / 2) {
          enemy.y = gameAreaHeight - enemy.height / 2;
          if (enemy.body) {
            const body = enemy.body as Phaser.Physics.Arcade.Body;
            body.y = enemy.y;
            body.setVelocityY(0);
          }
        }
      }

      if (enemy instanceof SideEnemy && enemy.isOffScreen()) {
        this.removeEnemy(enemy, i);
        continue;
      }

      const { width, height } = this.scale;
      if (enemy.x < -100 || enemy.x > width + 100 || enemy.y < -100 || enemy.y > height + 100) {
        this.removeEnemy(enemy, i);
      }
    }
  }

  private removeEnemy(enemy: BaseEnemy, index: number): void {
    this.enemies.splice(index, 1);
    enemy.destroy();
  }

  // ─── Player animations and death ─────────────────────────────────────────

  private playerWarpIn(): void {
    if (!this.player) return;

    const playerX = this.player.x;
    const playerY = this.player.y;
    const originalScaleX = this.player.scaleX;
    const originalScaleY = this.player.scaleY;

    this.player.setAlpha(0);
    this.player.setScale(0.05);
    this.player.setRotation(Phaser.Math.DegToRad(360));

    try {
      if (this.cache.audio.exists('danger')) {
        this.sound.play('danger', { volume: 0.3, detune: -1200 });
      }
    } catch {
      // ignore
    }

    const portalRing1 = this.add.circle(playerX, playerY, 10, 0x00ffff, 0);
    portalRing1.setStrokeStyle(3, 0x00ffff, 0.8);
    portalRing1.setDepth(15);
    const portalRing2 = this.add.circle(playerX, playerY, 10, 0x0080ff, 0);
    portalRing2.setStrokeStyle(3, 0x0080ff, 0.6);
    portalRing2.setDepth(15);
    const portalRing3 = this.add.circle(playerX, playerY, 10, 0xffffff, 0);
    portalRing3.setStrokeStyle(2, 0xffffff, 0.4);
    portalRing3.setDepth(15);

    this.tweens.add({ targets: portalRing1, radius: 200, alpha: { from: 0.8, to: 0 }, duration: 1500, ease: 'Power2', onComplete: () => portalRing1.destroy() });
    this.tweens.add({ targets: portalRing2, radius: 250, alpha: { from: 0.6, to: 0 }, duration: 1800, delay: 100, ease: 'Power2', onComplete: () => portalRing2.destroy() });
    this.tweens.add({ targets: portalRing3, radius: 300, alpha: { from: 0.4, to: 0 }, duration: 2000, delay: 200, ease: 'Power2', onComplete: () => portalRing3.destroy() });

    const particles = this.add.particles(playerX, playerY, undefined, {
      speed: { min: 100, max: 300 },
      scale: { start: 0.5, end: 0 },
      lifespan: 800,
      quantity: 50,
      tint: [0x00ffff, 0xffffff, 0x0080ff, 0xff00ff],
      blendMode: 'ADD',
      emitZone: { type: 'edge', source: new Phaser.Geom.Circle(0, 0, 50), quantity: 50 },
    });
    particles.setDepth(14);
    this.time.delayedCall(400, () => particles.stop());
    this.time.delayedCall(1200, () => particles.destroy());

    const energySwirl = this.add.graphics();
    energySwirl.setDepth(14);
    energySwirl.lineStyle(4, 0x00ffff, 0.6);
    energySwirl.strokeCircle(0, 0, 80);
    energySwirl.x = playerX;
    energySwirl.y = playerY;
    this.tweens.add({ targets: energySwirl, rotation: Math.PI * 4, scale: { from: 0.5, to: 2 }, alpha: { from: 0.6, to: 0 }, duration: 1500, ease: 'Power2', onComplete: () => energySwirl.destroy() });

    const flash = this.add.circle(playerX, playerY, 5, 0xffffff, 1);
    flash.setDepth(16);
    this.tweens.add({ targets: flash, radius: 150, alpha: { from: 1, to: 0 }, duration: 1200, ease: 'Power2', onComplete: () => flash.destroy() });

    this.tweens.add({ targets: this.player, rotation: 0, scaleX: originalScaleX * 0.3, scaleY: originalScaleY * 0.3, alpha: 0.3, duration: 600, ease: 'Power1' });
    this.tweens.add({ targets: this.player, scaleX: originalScaleX * 1.5, scaleY: originalScaleY * 1.5, alpha: 1, duration: 400, delay: 600, ease: 'Power3' });
    this.tweens.add({
      targets: this.player,
      scaleX: originalScaleX,
      scaleY: originalScaleY,
      duration: 500,
      delay: 1000,
      ease: 'Elastic.easeOut',
      onComplete: () => {
        if (this.player) {
          this.player.setScale(originalScaleX, originalScaleY);
          this.player.setRotation(0);
        }
      },
    });

    this.player.setTint(0x00ffff);
    this.time.delayedCall(1000, () => {
      if (this.player) {
        this.tweens.add({ targets: this.player, tintTopLeft: 0xffffff, tintTopRight: 0xffffff, tintBottomLeft: 0xffffff, tintBottomRight: 0xffffff, duration: 500 });
      }
    });
  }

  private playerWarpOut(): void {
    if (!this.player) return;

    const playerX = this.player.x;
    const playerY = this.player.y;

    this.isGameOver = true;

    const flash = this.add.circle(playerX, playerY, 100, 0xffffff, 1);
    flash.setDepth(15);

    const particles = this.add.particles(playerX, playerY, undefined, {
      speed: { min: 50, max: 200 },
      scale: { start: 0.5, end: 0 },
      lifespan: 600,
      quantity: 30,
      tint: [0x00ffff, 0xffffff, 0x0080ff],
      blendMode: 'ADD',
      emitZone: { type: 'edge', source: new Phaser.Geom.Circle(0, 0, 40), quantity: 30 },
    });
    particles.setDepth(14);

    this.tweens.add({ targets: this.player, scaleX: 0.1, scaleY: 0.1, alpha: 0, duration: 800, ease: 'Power2' });
    this.tweens.add({
      targets: flash,
      scale: { from: 1, to: 5 },
      alpha: { from: 1, to: 0 },
      duration: 800,
      onComplete: () => { flash.destroy(); particles.destroy(); },
    });

    this.time.delayedCall(2000, () => {
      if (this.backgroundMusic && this.backgroundMusic.isPlaying) {
        this.backgroundMusic.stop();
      }
      this.scene.start(SCENE_KEYS.EPISODE_2);
    });
  }

  private handlePlayerDeath(): void {
    if (this.isGameOver) return;
    this.isGameOver = true;

    if (this.player) {
      createExplosion(this, this.player.x, this.player.y, 1.5);
      this.player.setAlpha(0);
    }

    this.time.delayedCall(1000, () => {
      if (this.backgroundMusic && this.backgroundMusic.isPlaying) {
        this.backgroundMusic.stop();
      }
      this.scene.start(SCENE_KEYS.GAME_OVER, { score: this.score });
    });
  }

  // ─── Level complete sequence ──────────────────────────────────────────────

  private startLevelCompleteSequence(): void {
    if (!this.player) return;

    if (this.player.body && 'setVelocity' in this.player.body) {
      this.player.body.setVelocity(0, 0);
    }

    try {
      if (this.cache.audio.exists('danger')) {
        this.sound.play('danger', { volume: 0.175 });
      }
    } catch (e) {
      console.warn('Could not play level complete sound:', e);
    }

    const enemiesToDestroy: Array<{ enemy: BaseEnemy; delay: number }> = [];
    this.enemies.forEach((enemy, index) => {
      if (enemy.active) {
        enemiesToDestroy.push({ enemy, delay: index * 100 });
      }
    });

    if (this.spawnController.boss?.active) {
      enemiesToDestroy.push({ enemy: this.spawnController.boss, delay: enemiesToDestroy.length * 100 });
    }

    enemiesToDestroy.forEach(({ enemy, delay }) => {
      this.time.delayedCall(delay, () => {
        if (!enemy.active) return;
        createExplosion(this, enemy.x, enemy.y, enemy instanceof BossEnemy ? 2 : 1);
        if (enemy instanceof GroundEnemy) {
          this.addScore(SCORE_CONFIG.groundEnemy);
        } else if (enemy instanceof SideEnemy) {
          this.addScore(SCORE_CONFIG.sideEnemy);
        } else if (enemy instanceof BossEnemy) {
          this.addScore(SCORE_CONFIG.boss);
        }
        try {
          if (this.cache.audio.exists('explosion')) {
            this.sound.play('explosion', { volume: 0.125 });
          }
        } catch {
          // ignore
        }
        enemy.destroy();
      });
    });

    const messageDelay = enemiesToDestroy.length * 100 + 500;
    this.time.delayedCall(messageDelay, () => {
      this.showLevelCompleteMessage();
    });
  }

  private showLevelCompleteMessage(): void {
    const { width, height } = this.scale;

    const messageBg = this.add.rectangle(width / 2, height / 2, scaleSize(this, 600), scaleSize(this, 200), 0x000000, 0.8);
    messageBg.setStrokeStyle(3, 0x00ff00, 1);
    messageBg.setDepth(20);

    const missionText = this.add.text(width / 2, height / 2 - scaleSize(this, 30), 'MISSION COMPLETE', {
      fontSize: `${scaleSize(this, 36)}px`,
      color: '#00ff00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
      align: 'center',
    });
    missionText.setOrigin(0.5);
    missionText.setDepth(21);

    const hyperdriveText = this.add.text(width / 2, height / 2 + scaleSize(this, 30), 'ENGAGING HYPERDRIVE...', {
      fontSize: `${scaleSize(this, 24)}px`,
      color: '#00ffff',
      fontFamily: 'Arial',
      align: 'center',
    });
    hyperdriveText.setOrigin(0.5);
    hyperdriveText.setDepth(21);

    this.tweens.add({ targets: [missionText, hyperdriveText], alpha: { from: 1, to: 0.5 }, duration: 500, yoyo: true, repeat: 3, ease: 'Sine.easeInOut' });

    this.time.delayedCall(2500, () => {
      messageBg.destroy();
      missionText.destroy();
      hyperdriveText.destroy();
      this.playerWarpOut();
    });
  }
}
