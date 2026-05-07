import Phaser from 'phaser';
import { PLAYER_CONFIG, BULLET_CONFIG } from '../config';
import { getResponsiveSize } from '../utils/ResponsiveScale';
import { soundEffects } from '../utils/SoundEffects';
import { Bullet } from './Bullet';
import { TouchInputState } from '../utils/TouchControls';

/**
 * Player - The player's spaceship
 * 
 * Key concepts:
 * - Extends Phaser.Physics.Arcade.Sprite - gives us physics and collision
 * - Has movement controlled by arrow keys
 * - Can shoot bullets with spacebar
 * 
 * What is a Sprite?
 * - A sprite is a 2D image/object in the game (like your spaceship)
 * - Physics.Arcade.Sprite means it can move and collide with things
 */
export class Player extends Phaser.Physics.Arcade.Sprite {
  private maxSpeed: number;
  private acceleration: number;
  private deceleration: number;
  public cursors?: Phaser.Types.Input.Keyboard.CursorKeys;
  private spaceKey?: Phaser.Input.Keyboard.Key;
  private lastShotTime: number = 0; // Track when last bullet was fired
  public bullets: Bullet[] = []; // Array to store active bullets

  // Touch input state (for mobile)
  public touchInput?: TouchInputState;

  // Shield system
  private shieldGraphics?: Phaser.GameObjects.Arc;
  private currentShield: number;
  private maxShield: number;
  private currentHull: number;
  private maxHull: number;
  
  // Store original body size for restoring when shield is down
  private originalBodyWidth: number;
  private originalBodyHeight: number;

  // Health and Shield bars
  private healthBarBg?: Phaser.GameObjects.Rectangle;
  private healthBar?: Phaser.GameObjects.Rectangle;
  private shieldBarBg?: Phaser.GameObjects.Rectangle;
  private shieldBar?: Phaser.GameObjects.Rectangle;

  /**
   * Creates a new Player instance
   * 
   * @param scene - The scene this player belongs to (usually GameScene)
   * @param x - Starting X position
   * @param y - Starting Y position
   */
  constructor(scene: Phaser.Scene, x: number, y: number) {
    // Use the player texture
    const textureKey = 'player';
    
    // Check if RGB version of texture exists (RGBA versions don't render properly)
    const textureExists = scene.textures.exists(textureKey);
    console.log(`Player texture exists (${textureKey}): ${textureExists}`);
    
    if (!textureExists) {
      // Generate texture if image not loaded
      const graphics = scene.add.graphics();
      graphics.fillStyle(PLAYER_CONFIG.color, 1);
      graphics.beginPath();
      // Draw a triangle pointing up (like a spaceship)
      graphics.moveTo(0, -PLAYER_CONFIG.height / 2); // Top point
      graphics.lineTo(-PLAYER_CONFIG.width / 2, PLAYER_CONFIG.height / 2); // Bottom left
      graphics.lineTo(PLAYER_CONFIG.width / 2, PLAYER_CONFIG.height / 2); // Bottom right
      graphics.closePath();
      graphics.fillPath();

      // Generate a texture from the graphics
      graphics.generateTexture(textureKey, PLAYER_CONFIG.width, PLAYER_CONFIG.height);
      graphics.destroy(); // Remove the graphics object, we have the texture now
    }

    // Create the sprite with the texture (image or generated)
    super(scene, x, y, textureKey);

    // Note: When extending Phaser.GameObjects.Sprite, we need to add it to the scene manually
    scene.add.existing(this);
    
    // Enable physics for this sprite
    scene.physics.add.existing(this);

    // Set origin to center for proper rotation
    this.setOrigin(0.5, 0.5);
    
    // Make sure sprite is above background (background is depth -1)
    this.setDepth(10); // Higher depth to ensure visibility

    // Set size - use responsive scaling based on display dimensions
    const responsiveSize = getResponsiveSize(scene, PLAYER_CONFIG.width, PLAYER_CONFIG.height);
    
    if (textureExists) {
      // Use setDisplaySize with responsive scaling
      this.setDisplaySize(responsiveSize.width, responsiveSize.height);
      
      // Physics body size should match the display size
      this.setSize(responsiveSize.width, responsiveSize.height);
      
      // Explicitly set the frame
      const texture = scene.textures.get(textureKey);
      if (texture) {
        const frame = texture.get();
        if (frame) {
          this.setFrame(frame.name);
        }
      }
      
      console.log(`Player using image: ${textureKey}`);
      console.log(`  Position: ${this.x}, ${this.y}`);
      console.log(`  Display size: ${this.displayWidth}x${this.displayHeight}`);
      console.log(`  Scale: ${this.scaleX}x${this.scaleY}`);
      console.log(`  Texture key: ${this.texture?.key}`);
      console.log(`  Frame: ${this.frame?.name}`);
      console.log(`  Visible: ${this.visible}, Alpha: ${this.alpha}`);
      console.log(`  In scene: ${scene.children.exists(this)}`);
    } else {
      // Generated graphics - use responsive size
      this.setDisplaySize(responsiveSize.width, responsiveSize.height);
      this.setSize(responsiveSize.width, responsiveSize.height);
      console.log('Player using generated graphics');
    }
    
    // Ensure sprite is visible
    this.setVisible(true);
    this.setAlpha(1);
    this.clearTint();
    this.setActive(true);

    // Set up movement with acceleration
    this.maxSpeed = PLAYER_CONFIG.speed;
    this.acceleration = PLAYER_CONFIG.acceleration;
    this.deceleration = PLAYER_CONFIG.deceleration;
    
    // No drag - we handle deceleration manually for smooth control
    this.setDrag(0);
    
    // Get keyboard input
    this.cursors = scene.input.keyboard?.createCursorKeys();
    this.spaceKey = scene.input.keyboard?.addKey(Phaser.Input.Keyboard.KeyCodes.SPACE);

    // Prevent player from going off-screen
    this.setCollideWorldBounds(true);

    // Initialize hull and shield
    this.maxHull = PLAYER_CONFIG.maxHull;
    this.currentHull = this.maxHull;
    this.maxShield = PLAYER_CONFIG.maxShield;
    this.currentShield = this.maxShield;
    
    // Store original body size after it's been set
    // We need to wait until after setSize is called, so store it here
    // Store the original size (which was just set above)
    this.originalBodyWidth = responsiveSize.width;
    this.originalBodyHeight = responsiveSize.height;
    
    // Update body size based on shield status (may change it if shield is active)
    this.updateBodySizeForShield();

    // Create shield visual (circle around player)
    this.createShield();

    // Create health and shield bars
    this.createHealthBar();
    this.createShieldBar();
  }

  /**
   * Creates the shield visual - a circle that surrounds the player
   */
  private createShield(): void {
    // Create a circle graphic for the shield
    this.shieldGraphics = this.scene.add.circle(
      this.x,
      this.y,
      PLAYER_CONFIG.shieldRadius,
      PLAYER_CONFIG.shieldColor,
      PLAYER_CONFIG.shieldAlpha
    );
    
    // Set stroke style for a more defined shield look
    this.shieldGraphics.setStrokeStyle(2, PLAYER_CONFIG.shieldColor, 1);
    
    // Make sure shield is drawn above background but below player
    this.shieldGraphics.setDepth(0);
    this.setDepth(1); // Player should be above shield
  }

  /**
   * Updates the shield visual position to follow the player
   * Called every frame to keep shield centered on player
   */
  private updateShield(): void {
    if (this.shieldGraphics) {
      // Keep shield centered on player
      this.shieldGraphics.setPosition(this.x, this.y);
      
      // Update shield visibility based on current shield level
      if (this.currentShield <= 0) {
        this.shieldGraphics.setAlpha(0); // Hide shield if depleted
      } else {
        // Fade shield as it depletes (more transparent when lower)
        const shieldPercent = this.currentShield / this.maxShield;
        this.shieldGraphics.setAlpha(PLAYER_CONFIG.shieldAlpha * shieldPercent);
        
        // Optionally change color based on shield level (red when low)
        if (shieldPercent < 0.3) {
          // Low shield - red tint
          this.shieldGraphics.setFillStyle(0xff0000, PLAYER_CONFIG.shieldAlpha * shieldPercent);
        } else {
          // Normal shield - cyan
          this.shieldGraphics.setFillStyle(PLAYER_CONFIG.shieldColor, PLAYER_CONFIG.shieldAlpha * shieldPercent);
        }
      }
    }
    
    // Update body size based on shield status
    this.updateBodySizeForShield();
  }
  
  /**
   * Updates the physics body size based on shield status
   * When shields are active, body size matches shield radius
   * When shields are down, body size is normal ship size
   */
  private updateBodySizeForShield(): void {
    if (!this.body) return;
    
    const body = this.body as Phaser.Physics.Arcade.Body;
    const hasShield = this.currentShield > 0;
    
    if (hasShield) {
      // When shield is active, body size should be shield diameter (radius * 2)
      // Use responsive scaling for shield radius
      const responsiveShieldRadius = getResponsiveSize(this.scene, PLAYER_CONFIG.shieldRadius * 2, PLAYER_CONFIG.shieldRadius * 2);
      const shieldDiameter = responsiveShieldRadius.width;
      
      // Set body size to shield diameter
      body.setSize(shieldDiameter, shieldDiameter);
      
      // Center the larger body on the sprite by adjusting offset
      // Offset is negative half the difference between new size and original size
      const offsetX = (this.originalBodyWidth - shieldDiameter) / 2;
      const offsetY = (this.originalBodyHeight - shieldDiameter) / 2;
      body.setOffset(offsetX, offsetY);
    } else {
      // When shield is down, use original ship body size
      body.setSize(this.originalBodyWidth, this.originalBodyHeight);
      
      // Reset offset to center (0, 0) since body matches sprite size
      body.setOffset(0, 0);
    }
  }

  /**
   * Update is called every frame (60 times per second)
   * This is where we handle movement and rotation
   */
  update(): void {
    // Update shield visual to follow player
    this.updateShield();

    // Update health and shield bars
    this.updateHealthBar();
    this.updateShieldBar();

    // Get delta time from scene (in milliseconds), default to ~16.67ms for 60fps
    const deltaMs = this.scene.game.loop.delta;
    const deltaSeconds = deltaMs / 1000;
    
    // Get current velocity
    if (!this.body) return;
    const currentVelX = this.body.velocity.x;
    const currentVelY = this.body.velocity.y;
    
    // Calculate desired direction based on input
    let desiredVelX = 0;
    let desiredVelY = 0;

    // Get joystick input values (used for both rotation and movement)
    const joystickX = this.touchInput?.joystickX || 0;
    const joystickY = this.touchInput?.joystickY || 0;
    const hasJoystickInput = Math.abs(joystickX) > 0.1 || Math.abs(joystickY) > 0.1;

    // Handle rotation with left/right arrow keys OR touch input (joystick direction)
    const rotateLeft = this.cursors?.left.isDown || this.touchInput?.rotateLeft === true;
    const rotateRight = this.cursors?.right.isDown || this.touchInput?.rotateRight === true;
    
    if (hasJoystickInput) {
      // Auto-rotate towards joystick direction
      // Joystick: X is -1 (left) to 1 (right), Y is -1 (up) to 1 (down)
      // We need to invert Y because screen Y increases downward
      const targetAngleRad = Math.atan2(-joystickY, joystickX);
      const targetAngleDeg = Phaser.Math.RadToDeg(targetAngleRad) + 90; // Add 90 because ship points up at 0 degrees
      
      // Get current angle (normalized to 0-360)
      let currentAngle = this.angle;
      while (currentAngle < 0) currentAngle += 360;
      while (currentAngle >= 360) currentAngle -= 360;
      
      // Calculate shortest rotation path
      let angleDiff = targetAngleDeg - currentAngle;
      
      // Normalize angle difference to -180 to 180 range
      if (angleDiff > 180) {
        angleDiff -= 360;
      } else if (angleDiff < -180) {
        angleDiff += 360;
      }
      
      // Rotate towards target angle at natural speed
      const maxRotationStep = PLAYER_CONFIG.rotationSpeed * deltaSeconds;
      
      if (Math.abs(angleDiff) < maxRotationStep) {
        // Close enough, snap to target
        this.setAngle(targetAngleDeg);
        this.setAngularVelocity(0);
      } else {
        // Rotate towards target
        const rotationDirection = angleDiff > 0 ? 1 : -1;
        this.setAngularVelocity(PLAYER_CONFIG.rotationSpeed * rotationDirection);
      }
    } else if (rotateLeft) {
      // Keyboard rotation - rotate counter-clockwise (left)
      this.setAngularVelocity(-PLAYER_CONFIG.rotationSpeed);
    } else if (rotateRight) {
      // Keyboard rotation - rotate clockwise (right)
      this.setAngularVelocity(PLAYER_CONFIG.rotationSpeed);
    } else {
      // Stop rotation when keys are released (with smooth deceleration)
      if (this.body && 'angularVelocity' in this.body) {
        const currentAngularVel = this.body.angularVelocity;
        if (Math.abs(currentAngularVel) > 10) {
          // Gradually slow down rotation
          const angularDecel = currentAngularVel * 0.85;
          this.setAngularVelocity(angularDecel);
        } else {
          this.setAngularVelocity(0);
        }
      }
    }

    // Handle movement with acceleration
    // Support keyboard (speed dial), accelerator button, OR touch speed dial
    const moveForward = this.touchInput?.moveForward === true || this.touchInput?.accelerate === true;
    const moveBackward = this.touchInput?.moveBackward === true;
    
    // Check if using speed dial (from keyboard or touch)
    const speedDialValue = this.touchInput?.speed || 0;
    const usingSpeedDial = Math.abs(speedDialValue) > 0.1; // Use speed dial if value is set
    
    // Movement - forward/backward relative to ship facing
    const angleRad = Phaser.Math.DegToRad(this.angle - 90);
    
    // Calculate current speed magnitude to preserve momentum when rotating
    const currentSpeed = Math.sqrt(currentVelX * currentVelX + currentVelY * currentVelY);
    
    if (usingSpeedDial) {
      // Use speed dial value (-5 to 5, normalized to -1 to 1)
      const speedMultiplier = speedDialValue / 5; // -1 to 1
      desiredVelX = Math.cos(angleRad) * this.maxSpeed * speedMultiplier;
      desiredVelY = Math.sin(angleRad) * this.maxSpeed * speedMultiplier;
    } else if (moveForward) {
      // Accelerate forward in the direction the ship is facing (touch controls only)
      desiredVelX = Math.cos(angleRad) * this.maxSpeed;
      desiredVelY = Math.sin(angleRad) * this.maxSpeed;
    } else if (moveBackward) {
      // Accelerate backward (opposite direction of ship facing) (touch controls only)
      desiredVelX = Math.cos(angleRad + Math.PI) * this.maxSpeed;
      desiredVelY = Math.sin(angleRad + Math.PI) * this.maxSpeed;
    } else if (currentSpeed > 1) {
      // No new movement input, but ship has momentum - preserve speed, update direction based on current angle
      // This prevents speed from stopping when just rotating
      const speedMultiplier = currentSpeed / this.maxSpeed; // Preserve relative speed
      desiredVelX = Math.cos(angleRad) * this.maxSpeed * speedMultiplier;
      desiredVelY = Math.sin(angleRad) * this.maxSpeed * speedMultiplier;
    }

    // Apply acceleration/deceleration smoothly
    if (desiredVelX !== 0 || desiredVelY !== 0) {
      // Accelerate toward desired velocity
      const velDiffX = desiredVelX - currentVelX;
      const velDiffY = desiredVelY - currentVelY;
      
      // Calculate acceleration amount
      const accelAmount = this.acceleration * deltaSeconds;
      const accelX = Phaser.Math.Clamp(velDiffX, -accelAmount, accelAmount);
      const accelY = Phaser.Math.Clamp(velDiffY, -accelAmount, accelAmount);
      
      // Apply acceleration
      const newVelX = Phaser.Math.Clamp(
        currentVelX + accelX,
        -this.maxSpeed,
        this.maxSpeed
      );
      const newVelY = Phaser.Math.Clamp(
        currentVelY + accelY,
        -this.maxSpeed,
        this.maxSpeed
      );
      
      this.setVelocity(newVelX, newVelY);
    } else {
      // No input - decelerate smoothly (space friction)
      const decelAmount = this.deceleration * deltaSeconds;
      
      let newVelX = currentVelX;
      let newVelY = currentVelY;
      
      // Apply deceleration
      if (Math.abs(currentVelX) > decelAmount) {
        newVelX = currentVelX - Math.sign(currentVelX) * decelAmount;
      } else {
        newVelX = 0;
      }
      
      if (Math.abs(currentVelY) > decelAmount) {
        newVelY = currentVelY - Math.sign(currentVelY) * decelAmount;
      } else {
        newVelY = 0;
      }
      
      this.setVelocity(newVelX, newVelY);
    }
  }

  /**
   * Check if player is currently rotating (from keyboard input)
   * @returns true if left or right arrow keys are pressed
   */
  isRotating(): boolean {
    return (this.cursors?.left.isDown ?? false) || (this.cursors?.right.isDown ?? false);
  }

  /**
   * Get current speed as a value from -5 to 5
   * -5 = full reverse, 0 = stopped, +5 = full forward
   * Based on actual velocity relative to ship facing direction
   */
  getCurrentSpeed(): number {
    if (!this.body) return 0;
    
    const body = this.body as Phaser.Physics.Arcade.Body;
    const velX = body.velocity.x;
    const velY = body.velocity.y;
    
    // Calculate velocity magnitude (speed)
    const speed = Math.sqrt(velX * velX + velY * velY);
    
    // If no movement, return 0
    if (speed < 1) return 0;
    
    // Get ship forward direction vector
    const angleRad = Phaser.Math.DegToRad(this.angle - 90);
    const forwardX = Math.cos(angleRad);
    const forwardY = Math.sin(angleRad);
    
    // Normalize velocity vector
    const velNormX = velX / speed;
    const velNormY = velY / speed;
    
    // Dot product determines forward (positive) or backward (negative)
    const dotProduct = velNormX * forwardX + velNormY * forwardY;
    
    // Convert speed (0 to maxSpeed) to dial value (-5 to 5)
    // Use dot product sign to determine forward/backward
    const speedRatio = speed / this.maxSpeed; // 0 to 1
    const dialValue = dotProduct * speedRatio * 5; // -5 to 5
    
    // Round to nearest integer for cleaner display
    return Phaser.Math.Clamp(Math.round(dialValue), -5, 5);
  }

  /**
   * Check if spacebar is pressed OR touch shoot button is pressed
   * Used to determine if player wants to shoot
   */
  isShooting(): boolean {
    return (this.spaceKey?.isDown ?? false) || (this.touchInput?.shooting === true);
  }

  /**
   * Creates a new bullet if enough time has passed (cooldown)
   * Called from GameScene when player is shooting
   * 
   * @param scene - The scene to add the bullet to
   * @param currentTime - Current game time (for cooldown calculation)
   * @returns The created bullet, or null if still on cooldown
   */
  shoot(scene: Phaser.Scene, currentTime: number): Bullet | null {
    // Check if enough time has passed since last shot (cooldown)
    if (currentTime - this.lastShotTime < BULLET_CONFIG.cooldown) {
      return null; // Still on cooldown
    }

    // Play bullet firing sound
    try {
      if (scene.cache.audio.exists('bullet')) {
        scene.sound.play('bullet', { volume: 0.125 });
      }
    } catch (e) {
      // Ignore sound errors
    }

    // Calculate bullet spawn position (front of ship, in direction it's facing)
    const angleRad = Phaser.Math.DegToRad(this.angle - 90);
    const offsetX = Math.cos(angleRad) * (this.height / 2);
    const offsetY = Math.sin(angleRad) * (this.height / 2);
    
    // Create bullet at the front of the player, shooting in the direction the ship is facing
    const bullet = new Bullet(scene, this.x + offsetX, this.y + offsetY, this.angle);
    
    // Track this bullet
    this.bullets.push(bullet);
    
    // Update last shot time
    this.lastShotTime = currentTime;

    return bullet;
  }

  /**
   * Remove a bullet from tracking (when it's destroyed)
   */
  removeBullet(bullet: Bullet): void {
    const index = this.bullets.indexOf(bullet);
    if (index > -1) {
      this.bullets.splice(index, 1);
    }
  }

  /**
   * Get current shield value
   */
  getShield(): number {
    return this.currentShield;
  }

  /**
   * Get current hull value
   */
  getHull(): number {
    return this.currentHull;
  }

  /**
   * Get maximum shield value
   */
  getMaxShield(): number {
    return this.maxShield;
  }

  /**
   * Restore shield points (up to max)
   * @param amount - Amount of shield to restore
   */
  restoreShield(amount: number): void {
    this.currentShield = Math.min(this.currentShield + amount, this.maxShield);
  }

  /**
   * Get maximum hull value
   */
  getMaxHull(): number {
    return this.maxHull;
  }

  /**
   * Flash red when taking damage - multiple flashes for better visibility
   */
  private flashRed(): void {
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
   * Take damage - shield absorbs damage first, then hull
   * @param damage - Amount of damage to take
   */
  takeDamage(damage: number): void {
    // Visual feedback: flash red when hit with multiple flashes
    this.flashRed();

    // Shield absorbs damage first
    if (this.currentShield > 0) {
      this.currentShield -= damage;
      
      // Play shield damage sound
      soundEffects.playShieldDamage();
      
      if (this.currentShield < 0) {
        // If shield is depleted, remaining damage goes to hull
        const remainingDamage = Math.abs(this.currentShield);
        this.currentShield = 0;
        this.currentHull -= remainingDamage;
        
        // Play shield down sound
        soundEffects.playShieldDown();
        
        // Play hull damage sound when shield is depleted
        soundEffects.playHullDamage();
      }
    } else {
      // No shield, damage goes directly to hull
      this.currentHull -= damage;
      
      // Play hull damage sound
      soundEffects.playHullDamage();
    }

    // Prevent hull from going below 0
    if (this.currentHull < 0) {
      this.currentHull = 0;
    }
  }

  /**
   * Create health bar for player (red, above ship)
   */
  private createHealthBar(): void {
    const barWidth = 30;
    const barHeight = 4;
    const offsetY = -this.displayHeight / 2 - 10;
    
    // Background bar (dark red, always visible)
    this.healthBarBg = this.scene.add.rectangle(0, offsetY, barWidth, barHeight, 0x330000, 0.8);
    this.healthBarBg.setOrigin(0.5, 0.5);
    this.healthBarBg.setDepth(100);
    
    // Health bar (red, shows current health)
    this.healthBar = this.scene.add.rectangle(0, offsetY, barWidth, barHeight, 0xff0000, 1);
    this.healthBar.setOrigin(0.5, 0.5);
    this.healthBar.setDepth(101);
  }

  /**
   * Update health bar position and size
   */
  private updateHealthBar(): void {
    if (!this.healthBar || !this.healthBarBg || !this.active) return;
    
    // Update position to follow sprite
    const offsetY = -this.displayHeight / 2 - 10;
    this.healthBarBg.setPosition(this.x, this.y + offsetY);
    this.healthBar.setPosition(this.x, this.y + offsetY);
    
    // Update health bar width based on current hull
    const healthPercent = Math.max(0, this.currentHull / this.maxHull);
    const barWidth = 30;
    this.healthBar.setSize(barWidth * healthPercent, 4);
    this.healthBar.setOrigin(0.5, 0.5);
  }

  /**
   * Create shield bar for player (blue, smaller, below health bar)
   */
  private createShieldBar(): void {
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
  private updateShieldBar(): void {
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
   * Clean up shield graphics and bars when player is destroyed
   */
  destroy(): void {
    if (this.shieldGraphics) {
      this.shieldGraphics.destroy();
    }
    if (this.healthBarBg) {
      this.healthBarBg.destroy();
    }
    if (this.healthBar) {
      this.healthBar.destroy();
    }
    if (this.shieldBarBg) {
      this.shieldBarBg.destroy();
    }
    if (this.shieldBar) {
      this.shieldBar.destroy();
    }
    super.destroy();
  }
}

