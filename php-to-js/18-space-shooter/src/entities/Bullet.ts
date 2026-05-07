import Phaser from 'phaser';
import { BULLET_CONFIG } from '../config';
import { getResponsiveSize } from '../utils/ResponsiveScale';

/**
 * Bullet - A projectile fired by the player
 * 
 * Key concepts:
 * - Moves in the direction it was fired
 * - Gets destroyed when it goes off-screen
 * - Will collide with enemies later
 */
export class Bullet extends Phaser.Physics.Arcade.Sprite {
  private speed: number;
  private directionX: number;
  private directionY: number;

  /**
   * Creates a new Bullet instance
   * 
   * @param scene - The scene this bullet belongs to
   * @param x - Starting X position (same as player)
   * @param y - Starting Y position (top of player)
   * @param angle - Angle in degrees that the bullet should travel (0 = right, 90 = down)
   */
  constructor(scene: Phaser.Scene, x: number, y: number, angle: number = -90) {
    // Check if image exists, otherwise fall back to generated graphics
    const textureKey = 'bullet';
    
    if (!scene.textures.exists(textureKey)) {
      // Generate texture if image not loaded
      const graphics = scene.add.graphics();
      graphics.fillStyle(BULLET_CONFIG.color, 1);
      graphics.fillRect(0, 0, BULLET_CONFIG.width, BULLET_CONFIG.height);

      // Generate texture
      graphics.generateTexture(textureKey, BULLET_CONFIG.width, BULLET_CONFIG.height);
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

    // Set size - if image loaded, scale appropriately
    if (scene.textures.exists(textureKey)) {
      const texture = scene.textures.get(textureKey);
      let imageWidth = 128; // Default assumption
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
      const responsiveSize = getResponsiveSize(scene, BULLET_CONFIG.width, BULLET_CONFIG.height);
      this.setDisplaySize(responsiveSize.width, responsiveSize.height);
      this.setSize(responsiveSize.width, responsiveSize.height);
    }

    // Set speed
    this.speed = BULLET_CONFIG.speed;

    // Calculate direction based on angle
    // Convert angle to radians and offset by -90 (since 0 degrees = right, but we want 0 = up)
    const angleRad = Phaser.Math.DegToRad(angle - 90);
    this.directionX = Math.cos(angleRad);
    this.directionY = Math.sin(angleRad);

    // Set velocity in the calculated direction
    this.setVelocity(
      this.directionX * this.speed,
      this.directionY * this.speed
    );

    // Rotate bullet to match direction (add 90 degrees to the rotation we already set)
    this.setRotation(angleRad + Phaser.Math.DegToRad(90));
  }

  /**
   * Check if bullet has gone off-screen
   * Returns true if bullet should be destroyed
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

