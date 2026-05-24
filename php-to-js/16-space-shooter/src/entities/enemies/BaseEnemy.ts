import Phaser from 'phaser';
import { EnemyBullet } from './EnemyBullet';
import { soundEffects } from '../../utils/SoundEffects';

/**
 * BaseEnemy - Base class for all enemies
 * 
 * Key concepts:
 * - All enemies extend this class
 * - Provides common functionality (health, shooting, movement)
 * - Each enemy type can override methods to customize behavior
 * 
 * This is a modular design - easy to add new enemy types!
 */
export abstract class BaseEnemy extends Phaser.Physics.Arcade.Sprite {
  protected health: number;
  protected maxHealth: number;
  protected speed: number;
  protected shootCooldown: number;
  protected lastShotTime: number = 0;
  public bullets: EnemyBullet[] = [];
  protected healthBarBg?: Phaser.GameObjects.Rectangle;
  protected healthBar?: Phaser.GameObjects.Rectangle;

  /**
   * Creates a new BaseEnemy instance
   * 
   * @param scene - The scene this enemy belongs to
   * @param x - Starting X position
   * @param y - Starting Y position
   * @param texture - Texture key for the sprite
   * @param health - Maximum health for this enemy
   * @param speed - Movement speed
   * @param shootCooldown - Time between shots (milliseconds)
   */
  constructor(
    scene: Phaser.Scene,
    x: number,
    y: number,
    texture: string,
    health: number,
    speed: number,
    shootCooldown: number
  ) {
    super(scene, x, y, texture);

    // Add to scene and enable physics
    scene.add.existing(this);
    scene.physics.add.existing(this);

    // Set properties
    this.maxHealth = health;
    this.health = health;
    this.speed = speed;
    this.shootCooldown = shootCooldown;
    
    // Create health bar
    this.createHealthBar();
  }

  /**
   * Create health bar for this enemy
   */
  protected createHealthBar(): void {
    const barWidth = 30;
    const barHeight = 4;
    const offsetY = -this.displayHeight / 2 - 10;
    
    // Background bar (red, always visible)
    this.healthBarBg = this.scene.add.rectangle(0, offsetY, barWidth, barHeight, 0x330000, 0.8);
    this.healthBarBg.setOrigin(0.5, 0.5);
    this.healthBarBg.setDepth(100);
    
    // Health bar (red, shows current health)
    this.healthBar = this.scene.add.rectangle(0, offsetY, barWidth, barHeight, 0xff0000, 1);
    this.healthBar.setOrigin(0.5, 0.5);
    this.healthBar.setDepth(101);
    
    // Add to this sprite's container (if we make it a container)
    // For now, they'll follow the sprite position
  }

  /**
   * Update health bar position and size
   */
  protected updateHealthBar(): void {
    if (!this.healthBar || !this.healthBarBg || !this.active) return;
    
    // Update position to follow sprite
    const offsetY = -this.displayHeight / 2 - 10;
    this.healthBarBg.setPosition(this.x, this.y + offsetY);
    this.healthBar.setPosition(this.x, this.y + offsetY);
    
    // Update health bar width based on current health
    const healthPercent = Math.max(0, this.health / this.maxHealth);
    const barWidth = 30;
    this.healthBar.setSize(barWidth * healthPercent, 4);
    this.healthBar.setOrigin(0.5, 0.5);
  }

  /**
   * Update is called every frame
   * Override this in subclasses to customize behavior
   */
  abstract update(player: Phaser.Physics.Arcade.Sprite, currentTime: number): void;

  /**
   * Create a bullet that shoots toward the player
   * Override this to customize bullet behavior
   */
  protected shootAtPlayer(
    scene: Phaser.Scene,
    player: Phaser.Physics.Arcade.Sprite,
    currentTime: number
  ): EnemyBullet | null {
    // Check cooldown
    if (currentTime - this.lastShotTime < this.shootCooldown) {
      return null;
    }

    // Play bullet firing sound
    try {
      if (scene.cache.audio.exists('bullet')) {
        scene.sound.play('bullet', { volume: 0.1 }); // Slightly quieter for enemies
      }
    } catch (e) {
      // Ignore sound errors
    }

    // Calculate angle toward player
    const angle = Phaser.Math.Angle.Between(
      this.x,
      this.y,
      player.x,
      player.y
    );

    // Convert angle from radians to degrees
    const angleDegrees = Phaser.Math.RadToDeg(angle) + 90; // +90 to account for sprite orientation

    // Create bullet
    const bullet = new EnemyBullet(scene, this.x, this.y, angleDegrees);
    this.bullets.push(bullet);

    this.lastShotTime = currentTime;
    return bullet;
  }

  /**
   * Take damage - returns true if enemy is destroyed
   */
  takeDamage(damage: number): boolean {
    this.health -= damage;
    
    // Play enemy damage sound
    soundEffects.playEnemyDamage();
    
    // Visual feedback: flash red when hit with multiple flashes
    this.flashRed();
    
    if (this.health <= 0) {
      this.health = 0;
      return true; // Enemy destroyed
    }
    
    return false; // Still alive
  }

  /**
   * Flash red when taking damage - multiple flashes for better visibility
   */
  protected flashRed(): void {
    // Create multiple flashes for better visibility
    const flashCount = 3;
    const flashDuration = 80;
    
    for (let i = 0; i < flashCount; i++) {
      this.scene.time.delayedCall(i * flashDuration, () => {
        if (this.active) {
          this.setTint(0xff0000); // Red flash
        }
      });
      
      this.scene.time.delayedCall(i * flashDuration + flashDuration / 2, () => {
        if (this.active) {
          this.clearTint(); // Remove flash
        }
      });
    }
  }

  /**
   * Get current health
   */
  getHealth(): number {
    return this.health;
  }

  /**
   * Remove a bullet from tracking
   */
  removeBullet(bullet: EnemyBullet): void {
    const index = this.bullets.indexOf(bullet);
    if (index > -1) {
      this.bullets.splice(index, 1);
    }
  }

  /**
   * Cleanup when enemy is destroyed
   */
  destroy(): void {
    // Destroy all bullets
    this.bullets.forEach(bullet => bullet.destroy());
    this.bullets = [];
    
    // Destroy health bars
    if (this.healthBar) {
      this.healthBar.destroy();
    }
    if (this.healthBarBg) {
      this.healthBarBg.destroy();
    }
    
    super.destroy();
  }
}

