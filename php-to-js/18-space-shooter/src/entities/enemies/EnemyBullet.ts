import Phaser from 'phaser';
import { getResponsiveSize } from '../../utils/ResponsiveScale';

/**
 * EnemyBullet - Bullets fired by enemies
 * Similar to player bullets but red color and different behavior
 */
export class EnemyBullet extends Phaser.Physics.Arcade.Sprite {
  private speed: number;

  /**
   * Creates a new EnemyBullet instance
   * 
   * @param scene - The scene this bullet belongs to
   * @param x - Starting X position
   * @param y - Starting Y position
   * @param angle - Angle in degrees that the bullet should travel
   */
  constructor(scene: Phaser.Scene, x: number, y: number, angle: number) {
    // Check if image exists, otherwise fall back to generated graphics
    const textureKey = 'enemyBullet';
    
    if (!scene.textures.exists(textureKey)) {
      // Generate texture if image not loaded
      const graphics = scene.add.graphics();
      graphics.fillStyle(0xff0000, 1); // Red color for enemy bullets
      graphics.fillRect(0, 0, 6, 10); // Slightly bigger than player bullets

      // Generate texture
      graphics.generateTexture(textureKey, 6, 10);
      graphics.destroy();
    }

    // Create the sprite with the texture (image or generated)
    super(scene, x, y, textureKey);

    // Add to scene and enable physics
    scene.add.existing(this);
    scene.physics.add.existing(this);

    // Set origin to center
    this.setOrigin(0.5, 0.5);
    
    // Make sure bullets are visible (above background)
    this.setDepth(1);

    // Set size - use half PNG size and rotate 90 degrees
    if (scene.textures.exists(textureKey)) {
      const texture = scene.textures.get(textureKey);
      let imageWidth = 64; // Default assumption
      let imageHeight = 128;
      
      if (texture) {
        const frame = texture.get();
        if (frame) {
          imageWidth = frame.width;
          imageHeight = frame.height;
          this.setFrame(frame.name);
        }
      }
      
      // Use half PNG size, then scale responsively
      const baseScaledWidth = imageWidth / 2;
      const baseScaledHeight = imageHeight / 2;
      const responsiveSize = getResponsiveSize(scene, baseScaledWidth, baseScaledHeight);
      this.setDisplaySize(responsiveSize.width, responsiveSize.height);
      this.setSize(responsiveSize.width, responsiveSize.height);
      
      // Rotate bullet 90 degrees
      this.setRotation(Phaser.Math.DegToRad(90));
    } else {
      // Fallback - use responsive size
      const responsiveSize = getResponsiveSize(scene, 32, 64);
      this.setDisplaySize(responsiveSize.width, responsiveSize.height);
      this.setSize(responsiveSize.width, responsiveSize.height);
    }

    // Set speed (slower than player bullets)
    this.speed = 300;

    // Calculate direction based on angle
    const angleRad = Phaser.Math.DegToRad(angle - 90);
    const velocityX = Math.cos(angleRad) * this.speed;
    const velocityY = Math.sin(angleRad) * this.speed;

    // Set velocity in the calculated direction
    this.setVelocity(velocityX, velocityY);

    // Rotate bullet to match direction (add 90 degrees to the rotation we already set)
    this.setRotation(angleRad + Phaser.Math.DegToRad(90));
  }

  /**
   * Check if bullet has gone off-screen
   */
  isOffScreen(): boolean {
    if (!this.scene || !this.active) return true; // If scene is gone or bullet is inactive, consider it off-screen
    const { width, height } = this.scene.scale;
    return (
      this.x < -this.width ||
      this.x > width + this.width ||
      this.y < -this.height ||
      this.y > height + this.height
    );
  }
}

