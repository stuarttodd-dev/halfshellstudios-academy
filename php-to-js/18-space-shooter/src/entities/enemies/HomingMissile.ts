import Phaser from 'phaser';

/**
 * HomingMissile - A bullet that tracks and follows the player
 * Extends EnemyBullet behavior with homing capability
 */
export class HomingMissile extends Phaser.Physics.Arcade.Sprite {
  private speed: number;
  private homingStrength: number; // How strongly the missile homes in (0-1)
  private target?: Phaser.Physics.Arcade.Sprite;
  private turnSpeed: number; // Maximum degrees per frame the missile can turn

  /**
   * Creates a new HomingMissile instance
   * 
   * @param scene - The scene this missile belongs to
   * @param x - Starting X position
   * @param y - Starting Y position
   * @param initialAngle - Initial angle in degrees (will adjust to track target)
   * @param target - The target sprite to home in on (player)
   */
  constructor(
    scene: Phaser.Scene,
    x: number,
    y: number,
    initialAngle: number,
    target?: Phaser.Physics.Arcade.Sprite
  ) {
    // Use enemyBullet texture
    const textureKey = 'enemyBullet';
    
    if (!scene.textures.exists(textureKey)) {
      // Generate texture if image not loaded
      const graphics = scene.add.graphics();
      graphics.fillStyle(0xff6600, 1); // Orange color for homing missiles
      graphics.fillRect(0, 0, 6, 10);

      graphics.generateTexture(textureKey, 6, 10);
      graphics.destroy();
    }

    super(scene, x, y, textureKey);

    scene.add.existing(this);
    scene.physics.add.existing(this);

    this.setOrigin(0.5, 0.5);
    this.setDepth(1);

    // Set size (same as EnemyBullet)
    if (scene.textures.exists(textureKey)) {
      const texture = scene.textures.get(textureKey);
      let imageWidth = 64;
      let imageHeight = 128;
      
      if (texture) {
        const frame = texture.get();
        if (frame) {
          imageWidth = frame.width;
          imageHeight = frame.height;
          this.setFrame(frame.name);
        }
      }
      
      const scaledWidth = imageWidth / 2;
      const scaledHeight = imageHeight / 2;
      this.setDisplaySize(scaledWidth, scaledHeight);
      this.setSize(scaledWidth, scaledHeight);
      this.setRotation(Phaser.Math.DegToRad(90));
    } else {
      this.setDisplaySize(32, 64);
      this.setSize(32, 64);
    }

    // Homing missile properties
    this.speed = 250; // Slightly slower than regular bullets
    this.homingStrength = 0.15; // How much to adjust direction each frame
    this.turnSpeed = 5; // Maximum degrees per frame
    this.target = target;

    // Set initial velocity
    const angleRad = Phaser.Math.DegToRad(initialAngle - 90);
    const velocityX = Math.cos(angleRad) * this.speed;
    const velocityY = Math.sin(angleRad) * this.speed;
    this.setVelocity(velocityX, velocityY);
    this.setRotation(angleRad + Phaser.Math.DegToRad(90));
  }

  /**
   * Update the missile's direction to home in on target
   * Call this every frame for homing behavior
   */
  update(): void {
    if (!this.target || !this.target.active) {
      return; // No target or target is destroyed
    }

    // Calculate angle to target
    const angleToTarget = Phaser.Math.Angle.Between(
      this.x,
      this.y,
      this.target.x,
      this.target.y
    );
    
    // Current angle of movement
    if (!this.body) return;
    const currentAngle = Math.atan2(this.body.velocity.y, this.body.velocity.x);
    
    // Calculate angle difference
    let angleDiff = angleToTarget - currentAngle;
    
    // Normalize angle difference to -PI to PI
    while (angleDiff > Math.PI) angleDiff -= 2 * Math.PI;
    while (angleDiff < -Math.PI) angleDiff += 2 * Math.PI;
    
    // Limit turn rate
    const maxTurn = Phaser.Math.DegToRad(this.turnSpeed);
    if (Math.abs(angleDiff) > maxTurn) {
      angleDiff = Math.sign(angleDiff) * maxTurn;
    }
    
    // Calculate new angle
    const newAngle = currentAngle + (angleDiff * this.homingStrength);
    
    // Update velocity
    const newVelocityX = Math.cos(newAngle) * this.speed;
    const newVelocityY = Math.sin(newAngle) * this.speed;
    this.setVelocity(newVelocityX, newVelocityY);
    
    // Update rotation to match direction
    this.setRotation(newAngle + Phaser.Math.DegToRad(90));
  }

  /**
   * Check if missile has gone off-screen
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

