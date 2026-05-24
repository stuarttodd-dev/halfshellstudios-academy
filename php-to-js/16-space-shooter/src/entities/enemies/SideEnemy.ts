import Phaser from 'phaser';
import { BaseEnemy } from './BaseEnemy';
import { getResponsiveSize } from '../../utils/ResponsiveScale';

/**
 * SideEnemy - Enemy that appears from the sides of the screen
 * Moves across and shoots toward the player
 */
export class SideEnemy extends BaseEnemy {
  private direction: number; // -1 for left side (moves right), 1 for right side (moves left)

  constructor(scene: Phaser.Scene, x: number, y: number, fromLeft: boolean = true) {
    // Check if image exists, otherwise fall back to generated graphics
    const textureKey = 'sideEnemy';
    
    if (!scene.textures.exists(textureKey)) {
      // Generate texture if image not loaded
      const graphics = scene.add.graphics();
      graphics.fillStyle(0xff6600, 1); // Orange/red
      graphics.fillRect(-16, -16, 32, 32);

      // Generate texture
      graphics.generateTexture(textureKey, 32, 32);
      graphics.destroy();
    }

    // Call parent constructor
    super(scene, x, y, textureKey, 20, 100, 6000); // health: 20, speed: 100, shoot cooldown: 6000ms (6 seconds)

    // Set origin to center
    this.setOrigin(0.5, 0.5);
    
    // Make sure enemy is visible (above background)
    this.setDepth(1);

    // Set size - use responsive scaling
    const baseSize = 64;
    const responsiveSize = getResponsiveSize(scene, baseSize, baseSize);
    
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

    // Set direction based on which side they spawn from
    this.direction = fromLeft ? 1 : -1; // Left side moves right, right side moves left
  }

  /**
   * Update behavior - Side enemies move across screen and shoot at player
   * Bounces off boundaries instead of going off-screen
   */
  update(player: Phaser.Physics.Arcade.Sprite, currentTime: number): void {
    // Check if enemy hit a boundary and reverse direction
    const { width } = this.scene.scale;
    const margin = 30; // Margin to detect boundary hit
    
    // Check left boundary
    if (this.x <= margin && this.direction < 0) {
      this.direction = 1; // Reverse to move right
    }
    // Check right boundary
    if (this.x >= width - margin && this.direction > 0) {
      this.direction = -1; // Reverse to move left
    }
    
    // Move horizontally across the screen
    this.setVelocityX(this.speed * this.direction);

    // Shoot at player periodically
    this.shootAtPlayer(this.scene, player, currentTime);

    // Update bullets
    this.updateBullets();
    
    // Update health bar
    this.updateHealthBar();
  }

  /**
   * Check if enemy has moved off-screen (and should be removed)
   * Note: Enemies now bounce, so this is mainly for cleanup if they somehow get stuck
   */
  isOffScreen(): boolean {
    const { width } = this.scene.scale;
    return (this.x < -100) || (this.x > width + 100);
  }

  /**
   * Update and cleanup bullets
   */
  private updateBullets(): void {
    for (let i = this.bullets.length - 1; i >= 0; i--) {
      const bullet = this.bullets[i];
      if (bullet.isOffScreen()) {
        this.removeBullet(bullet);
        bullet.destroy();
      }
    }
  }
}

