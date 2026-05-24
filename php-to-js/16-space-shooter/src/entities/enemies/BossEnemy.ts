import Phaser from 'phaser';
import { BaseEnemy } from './BaseEnemy';
import { EnemyBullet } from './EnemyBullet';
import { HomingMissile } from './HomingMissile';
import { soundEffects } from '../../utils/SoundEffects';
import { getResponsiveSize } from '../../utils/ResponsiveScale';

/**
 * BossEnemy - Large boss enemy with shields and multiple attack patterns
 */
export class BossEnemy extends BaseEnemy {
  private maxShield: number;
  private currentShield: number;
  private shieldGraphics?: Phaser.GameObjects.Arc;
  // Removed lastAttackPattern - not currently used
  private attackCooldown: number = 0;
  public homingMissiles: HomingMissile[] = []; // Track homing missiles separately
  private horizontalDirection: number = 1; // 1 for right, -1 for left
  private bossSpeed: number = 100; // Horizontal movement speed
  protected shieldBarBg?: Phaser.GameObjects.Rectangle;
  protected shieldBar?: Phaser.GameObjects.Rectangle;

  constructor(scene: Phaser.Scene, x: number, y: number) {
    // Check if image exists, otherwise fall back to generated graphics
    const textureKey = 'bossEnemy';
    
    if (!scene.textures.exists(textureKey)) {
      // Generate texture if image not loaded
      const graphics = scene.add.graphics();
      graphics.fillStyle(0x8b0000, 1); // Dark red
      graphics.fillRect(-40, -30, 80, 60); // Larger rectangle
      // Add some detail
      graphics.fillStyle(0xff0000, 1);
      graphics.fillRect(-30, -20, 60, 40);

      // Generate texture
      graphics.generateTexture(textureKey, 80, 60);
      graphics.destroy();
    }

    // Call parent constructor with high health (speed doubled: 50 -> 100)
    super(scene, x, y, textureKey, 500, 100, 2000); // health: 500, speed: 100 (doubled), shoot cooldown: 2000ms

    // Set origin to center
    this.setOrigin(0.5, 0.5);
    
    // Make sure boss is visible (above background)
    this.setDepth(2);

    // Set size - use responsive scaling
    const baseWidth = 160;
    const baseHeight = 120;
    const responsiveSize = getResponsiveSize(scene, baseWidth, baseHeight);
    
    if (scene.textures.exists(textureKey)) {
      const texture = scene.textures.get(textureKey);
      if (texture) {
        const frame = texture.get();
        if (frame) {
          this.setFrame(frame.name);
        }
      }
      
      // Use responsive display size
      this.setDisplaySize(responsiveSize.width, responsiveSize.height);
      this.setSize(responsiveSize.width, responsiveSize.height);
    } else {
      this.setDisplaySize(responsiveSize.width, responsiveSize.height);
      this.setSize(responsiveSize.width, responsiveSize.height);
    }

    // Initialize shield system
    this.maxShield = 500;
    this.currentShield = 500;

    // Create shield visual
    this.createShield();
    
    // Create shield bar (blue, smaller)
    this.createShieldBar();

    // Reset attack cooldown
    this.attackCooldown = 0;
  }

  /**
   * Creates the shield visual - a circle that surrounds the boss
   */
  private createShield(): void {
    this.shieldGraphics = this.scene.add.circle(
      this.x,
      this.y,
      50, // Larger shield radius
      0xff00ff, // Magenta/purple shield
      0.4 // Less transparent
    );

    this.shieldGraphics.setStrokeStyle(3, 0xff00ff, 1);
    this.shieldGraphics.setDepth(1);
    this.setDepth(2);
  }

  /**
   * Updates the shield visual position and opacity
   */
  private updateShield(): void {
    if (this.shieldGraphics) {
      this.shieldGraphics.setPosition(this.x, this.y);

      if (this.currentShield <= 0) {
        this.shieldGraphics.setAlpha(0);
      } else {
        const shieldPercent = this.currentShield / this.maxShield;
        this.shieldGraphics.setAlpha(0.4 * shieldPercent);

        if (shieldPercent < 0.3) {
          this.shieldGraphics.setFillStyle(0xff0000, 0.4 * shieldPercent);
        } else {
          this.shieldGraphics.setFillStyle(0xff00ff, 0.4 * shieldPercent);
        }
      }
    }
  }

  /**
   * Override takeDamage to handle shield
   */
  override takeDamage(damage: number): boolean {
    // Shield absorbs damage first
    if (this.currentShield > 0) {
      // Shield absorbs damage
      this.currentShield -= damage;
      
      // Play shield damage sound for boss
      soundEffects.playShieldDamage();
      
      // Check if shield was depleted (reached 0 or below)
      if (this.currentShield <= 0) {
        const remainingDamage = this.currentShield < 0 ? Math.abs(this.currentShield) : 0;
        this.currentShield = 0;
        this.health -= remainingDamage;
        
        // Play shield down sound for boss when shield reaches 0
        soundEffects.playShieldDown();
        
        // Play hull damage sound
        if (remainingDamage > 0) {
          soundEffects.playHullDamage();
        }
      }
    } else {
      this.health -= damage;
      
      // Play hull damage sound for boss
      soundEffects.playHullDamage();
    }

    // Visual feedback: flash red when hit (inherits flashRed from BaseEnemy)
    this.flashRed();

    if (this.health <= 0) {
      this.health = 0;
      return true; // Boss destroyed
    }

    return false; // Still alive
  }

  /**
   * Get current shield value
   */
  getShield(): number {
    return this.currentShield;
  }

  /**
   * Restore/add shields (cannot exceed maxShield)
   */
  restoreShield(amount: number): void {
    this.currentShield = Math.min(this.currentShield + amount, this.maxShield);
  }

  /**
   * Create shield bar for boss (blue, smaller, below health bar)
   */
  protected createShieldBar(): void {
    const barWidth = 25;
    const barHeight = 3;
    const offsetY = -this.displayHeight / 2 - 5; // Just below health bar
    
    // Background bar (dark blue, always visible)
    this.shieldBarBg = this.scene.add.rectangle(0, offsetY, barWidth, barHeight, 0x000033, 0.8);
    this.shieldBarBg.setOrigin(0.5, 0.5);
    this.shieldBarBg.setDepth(100);
    
    // Shield bar (blue, shows current shield)
    this.shieldBar = this.scene.add.rectangle(0, offsetY, barWidth, barHeight, 0x0088ff, 1);
    this.shieldBar.setOrigin(0.5, 0.5);
    this.shieldBar.setDepth(101);
  }

  /**
   * Update shield bar position and size
   */
  protected updateShieldBar(): void {
    if (!this.shieldBar || !this.shieldBarBg || !this.active) return;
    
    // Update position to follow sprite (below health bar)
    const offsetY = -this.displayHeight / 2 - 5;
    this.shieldBarBg.setPosition(this.x, this.y + offsetY);
    this.shieldBar.setPosition(this.x, this.y + offsetY);
    
    // Update shield bar width based on current shield
    const shieldPercent = Math.max(0, this.currentShield / this.maxShield);
    const barWidth = 25;
    this.shieldBar.setSize(barWidth * shieldPercent, 3);
    this.shieldBar.setOrigin(0.5, 0.5);
  }

  /**
   * Update behavior - Boss has multiple attack patterns
   * Moves up and down, reversing direction at boundaries
   */
  update(player: Phaser.Physics.Arcade.Sprite, currentTime: number): void {
    // Check if boss is still active and has a body
    if (!this.active || !this.body) return;

    // Update shield visual
    this.updateShield();
    
    // Update health and shield bars
    this.updateHealthBar();
    this.updateShieldBar();

    // Get scene boundaries
    const { width } = this.scene.scale;
    const margin = 30; // Margin from screen edges
    
    // Check boundaries and reverse direction
    const bossHalfWidth = this.displayWidth / 2;
    const leftBoundary = bossHalfWidth + margin;
    const rightBoundary = width - bossHalfWidth - margin;
    
    // Reverse direction if hitting boundaries
    if (this.x <= leftBoundary && this.horizontalDirection < 0) {
      this.horizontalDirection = 1; // Change to moving right
    } else if (this.x >= rightBoundary && this.horizontalDirection > 0) {
      this.horizontalDirection = -1; // Change to moving left
    }
    
    // Move side to side
    this.setVelocityX(this.bossSpeed * this.horizontalDirection);
    this.setVelocityY(0); // No vertical movement

    // Attack with different patterns
    if (currentTime - this.attackCooldown > this.shootCooldown) {
      this.performAttack(player, currentTime);
      this.attackCooldown = currentTime;
    }

    // Update bullets
    this.updateBullets();
    
    // Update homing missiles
    this.updateHomingMissiles();
  }

  /**
   * Perform one of several attack patterns
   */
  private performAttack(player: Phaser.Physics.Arcade.Sprite, currentTime: number): void {
    // Cycle through attack patterns (now 5 patterns)
    const pattern = Math.floor(currentTime / 5000) % 5; // Change pattern every 5 seconds

    switch (pattern) {
      case 0:
        this.attackSingleShot(player);
        break;
      case 1:
        this.attackSpread(player);
        break;
      case 2:
        this.attackRapid(player);
        break;
      case 3:
        this.attackCrossLaser();
        break;
      case 4:
        this.attackHomingMissiles(player);
        break;
    }
  }

  /**
   * Attack Pattern 1: Single accurate shot
   */
  private attackSingleShot(player: Phaser.Physics.Arcade.Sprite): void {
    // Play bullet sound
    try {
      if (this.scene.cache.audio.exists('bullet')) {
        this.scene.sound.play('bullet', { volume: 0.1 });
      }
    } catch (e) {
      // Ignore sound errors
    }

    const angle = Phaser.Math.Angle.Between(this.x, this.y, player.x, player.y);
    const angleDegrees = Phaser.Math.RadToDeg(angle) + 90;
    const bullet = new EnemyBullet(this.scene, this.x, this.y + 30, angleDegrees);
    this.bullets.push(bullet);
  }

  /**
   * Attack Pattern 2: Spread shot (3 bullets in a cone)
   */
  private attackSpread(player: Phaser.Physics.Arcade.Sprite): void {
    // Play bullet sound
    try {
      if (this.scene.cache.audio.exists('bullet')) {
        this.scene.sound.play('bullet', { volume: 0.1 });
      }
    } catch (e) {
      // Ignore sound errors
    }

    const baseAngle = Phaser.Math.Angle.Between(this.x, this.y, player.x, player.y);
    const baseDegrees = Phaser.Math.RadToDeg(baseAngle) + 90;

    // Fire 3 bullets: center, left, right
    for (let i = -1; i <= 1; i++) {
      const angle = baseDegrees + (i * 20); // 20 degree spread
      const bullet = new EnemyBullet(this.scene, this.x, this.y + 30, angle);
      this.bullets.push(bullet);
    }
  }

  /**
   * Attack Pattern 3: Rapid fire (5 bullets in quick succession)
   */
  private attackRapid(player: Phaser.Physics.Arcade.Sprite): void {
    const angle = Phaser.Math.Angle.Between(this.x, this.y, player.x, player.y);
    const angleDegrees = Phaser.Math.RadToDeg(angle) + 90;

    // Fire 5 bullets with small delays
    for (let i = 0; i < 5; i++) {
      this.scene.time.delayedCall(i * 150, () => {
        if (this.active) {
          // Play bullet sound for each shot
          try {
            if (this.scene.cache.audio.exists('bullet')) {
              this.scene.sound.play('bullet', { volume: 0.0875 }); // Slightly quieter for rapid fire
            }
          } catch (e) {
            // Ignore sound errors
          }

          const bullet = new EnemyBullet(this.scene, this.x, this.y + 30, angleDegrees);
          this.bullets.push(bullet);
        }
      });
    }
  }

  /**
   * Attack Pattern 4: Cross Laser - 4 bullets in cardinal directions (up, down, left, right)
   */
  private attackCrossLaser(): void {
    // Play bullet sound
    try {
      if (this.scene.cache.audio.exists('bullet')) {
        this.scene.sound.play('bullet', { volume: 0.1 });
      }
    } catch (e) {
      // Ignore sound errors
    }

    const angles = [0, 90, 180, 270]; // Up, Right, Down, Left
    
    angles.forEach(angle => {
      const bullet = new EnemyBullet(this.scene, this.x, this.y + 30, angle);
      this.bullets.push(bullet);
    });
  }

  /**
   * Attack Pattern 5: Homing Missiles - Fire 3 missiles that track the player
   */
  private attackHomingMissiles(player: Phaser.Physics.Arcade.Sprite): void {
    // Play bullet sound for homing missiles
    try {
      if (this.scene.cache.audio.exists('bullet')) {
        this.scene.sound.play('bullet', { volume: 0.1 });
      }
    } catch (e) {
      // Ignore sound errors
    }

    // Calculate initial angles spread
    const baseAngle = Phaser.Math.Angle.Between(this.x, this.y, player.x, player.y);
    const baseDegrees = Phaser.Math.RadToDeg(baseAngle) + 90;
    
    // Fire 3 homing missiles with slight spread
    for (let i = -1; i <= 1; i++) {
      const angle = baseDegrees + (i * 15); // 15 degree spread
      const missile = new HomingMissile(
        this.scene,
        this.x,
        this.y + 30,
        angle,
        player
      );
      this.homingMissiles.push(missile);
    }
  }

  /**
   * Update and cleanup bullets
   */
  private updateBullets(): void {
    for (let i = this.bullets.length - 1; i >= 0; i--) {
      const bullet = this.bullets[i];
      if (!bullet || !bullet.active) {
        // Bullet is already destroyed, remove from array
        this.bullets.splice(i, 1);
        continue;
      }
      if (bullet.isOffScreen()) {
        this.removeBullet(bullet);
        bullet.destroy();
      }
    }
  }

  /**
   * Update and cleanup homing missiles
   */
  private updateHomingMissiles(): void {
    for (let i = this.homingMissiles.length - 1; i >= 0; i--) {
      const missile = this.homingMissiles[i];
      if (missile && missile.active) {
        missile.update(); // Update homing behavior
        if (missile.isOffScreen()) {
          this.homingMissiles.splice(i, 1);
          missile.destroy();
        }
      } else {
        this.homingMissiles.splice(i, 1);
      }
    }
  }

  /**
   * Cleanup when boss is destroyed
   */
  override destroy(): void {
    if (this.shieldGraphics) {
      this.shieldGraphics.destroy();
    }
    
    // Clean up health and shield bars
    if (this.healthBar) {
      this.healthBar.destroy();
    }
    if (this.healthBarBg) {
      this.healthBarBg.destroy();
    }
    if (this.shieldBar) {
      this.shieldBar.destroy();
    }
    if (this.shieldBarBg) {
      this.shieldBarBg.destroy();
    }
    
    // Destroy all homing missiles
    this.homingMissiles.forEach(missile => {
      if (missile && missile.active) {
        missile.destroy();
      }
    });
    this.homingMissiles = [];
    
    super.destroy();
  }
}

