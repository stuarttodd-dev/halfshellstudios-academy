import Phaser from 'phaser';
import { BaseEnemy } from './BaseEnemy';
import { getResponsiveSize } from '../../utils/ResponsiveScale';

/**
 * GroundEnemy - Enemy that appears at the bottom of the screen
 * Shoots upward toward the player
 */
export class GroundEnemy extends BaseEnemy {
  constructor(scene: Phaser.Scene, x: number, y: number) {
    // Check if image exists, otherwise fall back to generated graphics
    const textureKey = 'groundEnemy';
    
    if (!scene.textures.exists(textureKey)) {
      // Generate texture if image not loaded
      const graphics = scene.add.graphics();
      graphics.fillStyle(0xff0000, 1); // Red
      graphics.beginPath();
      graphics.moveTo(0, 16); // Bottom point (shooting up)
      graphics.lineTo(-16, -16); // Top left
      graphics.lineTo(16, -16); // Top right
      graphics.closePath();
      graphics.fillPath();

      // Generate texture
      graphics.generateTexture(textureKey, 32, 32);
      graphics.destroy();
    }

    // Call parent constructor
    super(scene, x, y, textureKey, 30, 50, 5000); // health: 30, speed: 50, shoot cooldown: 5000ms (5 seconds)

    // Set origin to center
    this.setOrigin(0.5, 0.5);
    
    // Make sure enemy is visible (above background)
    this.setDepth(1);

    // Set size - use responsive scaling with minimum size
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
  }

  /**
   * Update behavior - Ground enemies move slowly and shoot at player
   */
  update(player: Phaser.Physics.Arcade.Sprite, currentTime: number): void {
    // Ground enemies can move left/right slightly
    // For now, they stay mostly stationary and just shoot
    
    // Shoot at player periodically
    this.shootAtPlayer(this.scene, player, currentTime);

    // Update bullets (check if off-screen and remove)
    this.updateBullets();
    
    // Update health bar
    this.updateHealthBar();
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

