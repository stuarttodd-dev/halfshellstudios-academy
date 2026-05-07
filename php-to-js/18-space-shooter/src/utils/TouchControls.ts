import Phaser from 'phaser';

/**
 * TouchControls - Manages touch input for mobile devices
 * Provides virtual buttons for rotation, movement, and shooting
 */

export interface TouchInputState {
  rotateLeft: boolean;
  rotateRight: boolean;
  moveForward: boolean;
  moveBackward: boolean;
  shooting: boolean;
  accelerate: boolean; // Accelerator button
  speed: number; // Speed dial value from -5 to 5
  // Joystick input (normalized direction vector, -1 to 1) - for rotation only
  joystickX: number; // -1 (left) to 1 (right)
  joystickY: number; // -1 (up) to 1 (down)
}

/**
 * Detect if the device is mobile
 */
export function isMobileDevice(): boolean {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
    navigator.userAgent
  ) || (window.innerWidth <= 768);
}

/**
 * TouchControls class - manages on-screen touch buttons
 */
export class TouchControls {
  private scene: Phaser.Scene;
  private inputState: TouchInputState;
  private buttons: {
    rotateLeft?: Phaser.GameObjects.Container;
    rotateRight?: Phaser.GameObjects.Container;
    moveForward?: Phaser.GameObjects.Container;
    moveBackward?: Phaser.GameObjects.Container;
    shoot?: Phaser.GameObjects.Container;
  } = {};
  private fireButtonImage?: Phaser.GameObjects.Image; // Store fire button image for flash effect
  private joystick?: {
    base?: Phaser.GameObjects.Container;
    stick?: Phaser.GameObjects.Container;
    headingLine?: Phaser.GameObjects.Line;
    angleText?: Phaser.GameObjects.Text;
    centerX: number;
    centerY: number;
    radius: number;
    active: boolean;
    pointerId?: number;
  };
  private speedDial?: {
    container?: Phaser.GameObjects.Container;
    background?: Phaser.GameObjects.Rectangle;
    bars?: Phaser.GameObjects.Rectangle[];
    indicator?: Phaser.GameObjects.Rectangle;
    centerX: number;
    centerY: number;
    width: number;
    height: number;
    active: boolean;
    pointerId?: number;
    minY: number;
    maxY: number;
  };

  constructor(scene: Phaser.Scene) {
    this.scene = scene;
    this.inputState = {
      rotateLeft: false,
      rotateRight: false,
      moveForward: false,
      moveBackward: false,
      shooting: false,
      accelerate: false,
      speed: 0,
      joystickX: 0,
      joystickY: 0,
    };
  }

  /**
   * Create all touch control buttons (only on mobile)
   * Layout: Dashboard style with Fire + Accelerator on left, Rotation wheel on right
   */
  create(): void {
    // Always show controls (desktop and mobile)
    const { width, height } = this.scene.scale;

    // Dashboard styling
    const margin = 20;
    const joystickRadius = 100;
    const DASHBOARD_HEIGHT = 200; // Match GameScene constant
    // Position dashboard at the very bottom of the canvas
    // Center Y is at canvas height minus half the dashboard height
    const dashboardY = height - DASHBOARD_HEIGHT / 2;

    // Create dashboard background
    this.createDashboard(width, 0, dashboardY, DASHBOARD_HEIGHT);

    // LEFT SIDE - Fire button (quite large)
    const buttonSize = Math.round(80 * 1.33); // 33% bigger (was 80, now ~106)
    const leftMargin = 30;
    const fireButtonX = leftMargin + buttonSize / 2 + 100; // Moved right another 50px (total 100px)
    const fireButtonY = dashboardY - 50; // Moved up 50px

    this.buttons.shoot = this.createFireButton(
      fireButtonX,
      fireButtonY,
      buttonSize,
      () => {
        this.inputState.shooting = true;
        this.flashFireButton();
      },
      () => {
        this.inputState.shooting = false;
      }
    );

    // CENTER - Speed dial (bar chart style) - half size, moved right 50px, up 50px
    const speedDialX = width / 2; // Centered (moved left 100px from previous position)
    const speedDialY = dashboardY - 45; // Moved down 5px (was -50, now -45)
    const speedDialWidth = 35; // 15px smaller (was 50)
    const speedDialHeight = 70; // Half size (was 140)
    this.createSpeedDial(speedDialX, speedDialY, speedDialWidth, speedDialHeight);

    // RIGHT SIDE - Rotation wheel
    const rightPanelX = width - margin - joystickRadius;
    const rightPanelY = dashboardY;

    // Removed 'CURRENT HEADING' text label
    // Removed 'SPEED' text label

    this.createJoystick(rightPanelX, rightPanelY, joystickRadius);
  }

  /**
   * Create fire button with image background and flash effect
   */
  private createFireButton(
    x: number,
    y: number,
    size: number,
    onDown: () => void,
    onUp: () => void
  ): Phaser.GameObjects.Container {
    const container = this.scene.add.container(x, y);
    container.setDepth(100);

    // Use fire button image - already large size
    let buttonImage: Phaser.GameObjects.Image | Phaser.GameObjects.Rectangle;
    
    if (this.scene.textures.exists('fireButton')) {
      buttonImage = this.scene.add.image(0, 0, 'fireButton');
      buttonImage.setDisplaySize(size, size); // Use size directly (already large)
      buttonImage.setOrigin(0.5);
      this.fireButtonImage = buttonImage as Phaser.GameObjects.Image;
      console.log('✅ Fire button image displayed (large size)');
    } else {
      // Fallback to rectangle if image not loaded (no red border)
      buttonImage = this.scene.add.rectangle(0, 0, size, size, 0x001122, 1.0);
      console.log('⚠️ Fire button image not found, using fallback rectangle');
    }
    
    container.add(buttonImage);

    // Make interactive and tappable
    container.setSize(size, size);
    container.setInteractive({ useHandCursor: true });

    // Handle touch/pointer events - tapping fires
    container.on('pointerdown', () => {
      onDown(); // Sets shooting = true and flashes button
    });

    container.on('pointerup', () => {
      onUp(); // Sets shooting = false
    });

    container.on('pointerout', () => {
      onUp(); // Sets shooting = false when leaving button
    });

    // Prevent game actions when touching buttons
    container.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
      pointer.event.stopPropagation();
    });

    return container;
  }

  /**
   * Flash the fire button when pressed (called from touch or spacebar)
   */
  flashFireButton(): void {
    if (this.fireButtonImage) {
      // Create flash effect - brighten the button briefly
      this.scene.tweens.add({
        targets: this.fireButtonImage,
        alpha: 0.3,
        duration: 50,
        yoyo: true,
        ease: 'Power2',
        onComplete: () => {
          this.fireButtonImage?.setAlpha(1.0);
        }
      });
      
      // Also flash with white overlay
      const flashOverlay = this.scene.add.rectangle(
        this.fireButtonImage.x,
        this.fireButtonImage.y,
        this.fireButtonImage.displayWidth,
        this.fireButtonImage.displayHeight,
        0xffffff,
        0.8
      );
      flashOverlay.setDepth(101);
      flashOverlay.setOrigin(0.5);
      
      this.scene.tweens.add({
        targets: flashOverlay,
        alpha: 0,
        duration: 100,
        ease: 'Power2',
        onComplete: () => {
          flashOverlay.destroy();
        }
      });
    }
  }


  /**
   * Create dashboard background with panel image
   */
  private createDashboard(width: number, _height: number, centerY: number, dashboardHeight: number): void {
    // Use panel.png image if available, otherwise fall back to styled rectangle
    if (this.scene.textures.exists('panel')) {
      const panel = this.scene.add.image(
        width / 2,
        centerY,
        'panel'
      );
      
      // Get the original texture dimensions to calculate aspect ratio
      const texture = this.scene.textures.get('panel');
      const originalWidth = texture.getSourceImage().width;
      const originalHeight = texture.getSourceImage().height;
      const aspectRatio = originalWidth / originalHeight;
      
      // Set height to 100% of dashboard height, width adjusts automatically
      const scaledWidth = dashboardHeight * aspectRatio;
      panel.setDisplaySize(scaledWidth, dashboardHeight);
      panel.setDepth(99);
      console.log('✅ Panel background image displayed');
    } else {
      // Fallback to styled rectangle if image not loaded
      const dashboard = this.scene.add.rectangle(
        width / 2,
        centerY,
        width,
        dashboardHeight,
        0x000511,
        0.95
      );
      dashboard.setStrokeStyle(4, 0x00ff00, 1.0);
      dashboard.setDepth(99);
      console.log('⚠️ Panel image not found, using fallback rectangle');
    }
  }

  /**
   * Create a virtual joystick for rotation control (dashboard style with improved visuals)
   */
  private createJoystick(centerX: number, centerY: number, radius: number): void {
    // Base circle (outer ring) - dashboard style
    const baseContainer = this.scene.add.container(centerX, centerY);
    baseContainer.setDepth(100);

    // Outer glow
    const outerGlow = this.scene.add.circle(0, 0, radius + 3, 0x00ff00, 0.1);
    baseContainer.add(outerGlow);

    // Main base circle
    const baseCircle = this.scene.add.circle(0, 0, radius, 0x000511, 1.0);
    baseCircle.setStrokeStyle(4, 0x00ff00, 1.0);
    baseContainer.add(baseCircle);

    // Inner ring with gradient effect
    const innerRing = this.scene.add.circle(0, 0, radius * 0.75, 0x001122, 0.6);
    innerRing.setStrokeStyle(2, 0x00ff00, 0.5);
    baseContainer.add(innerRing);

    // Crosshair guide lines with better visibility
    const lineLength = radius * 0.85;
    const lineColor = 0x00ff00;
    const hLine = this.scene.add.line(0, 0, -lineLength, 0, lineLength, 0, lineColor, 0.4);
    const vLine = this.scene.add.line(0, 0, 0, -lineLength, 0, lineLength, lineColor, 0.4);
    hLine.setLineWidth(2);
    vLine.setLineWidth(2);
    baseContainer.add(hLine);
    baseContainer.add(vLine);

    // Center dot
    const centerDot = this.scene.add.circle(0, 0, 3, 0x00ff00, 1.0);
    baseContainer.add(centerDot);

    // Heading line (points in direction of ship) - will be updated dynamically
    const headingLine = this.scene.add.line(0, 0, 0, 0, 0, -radius * 0.9, 0xffff00, 1.0);
    headingLine.setLineWidth(3);
    headingLine.setOrigin(0, 0);
    baseContainer.add(headingLine);

    // Angle text display (below the circle)
    const angleText = this.scene.add.text(0, radius + 20, '000°', {
      fontSize: '14px',
      color: '#00ff00',
      fontFamily: 'Arial',
      align: 'center',
      fontStyle: 'bold',
      stroke: '#000000',
      strokeThickness: 1,
    });
    angleText.setOrigin(0.5);
    baseContainer.add(angleText);

    // Stick (inner circle that moves) with improved styling
    const stickContainer = this.scene.add.container(centerX, centerY);
    stickContainer.setDepth(101);

    const stickRadius = radius * 0.32;
    // Stick outer glow
    const stickGlow = this.scene.add.circle(0, 0, stickRadius + 2, 0x00ff00, 0.3);
    stickContainer.add(stickGlow);
    
    // Stick main body
    const stick = this.scene.add.circle(0, 0, stickRadius, 0x00ff00, 0.7);
    stick.setStrokeStyle(3, 0x00ff00, 1.0);
    stickContainer.add(stick);

    // Stick center highlight
    const stickCenter = this.scene.add.circle(0, 0, stickRadius * 0.4, 0x88ff88, 0.8);
    stickContainer.add(stickCenter);

    // Make base interactive
    baseContainer.setSize(radius * 2, radius * 2);
    baseContainer.setInteractive({ useHandCursor: false });

    // Store joystick state
    this.joystick = {
      base: baseContainer,
      stick: stickContainer,
      headingLine,
      angleText,
      centerX,
      centerY,
      radius,
      active: false,
    };

    // Handle touch events
    baseContainer.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
      this.joystick!.active = true;
      this.joystick!.pointerId = pointer.id;
      this.updateJoystick(pointer.x, pointer.y);
    });

    // Track pointer movement even outside the joystick area
    this.scene.input.on('pointermove', (pointer: Phaser.Input.Pointer) => {
      if (this.joystick?.active && this.joystick.pointerId === pointer.id) {
        this.updateJoystick(pointer.x, pointer.y);
      }
    });

    this.scene.input.on('pointerup', (pointer: Phaser.Input.Pointer) => {
      if (this.joystick?.active && this.joystick.pointerId === pointer.id) {
        this.resetJoystick();
      }
    });
  }

  /**
   * Update joystick position based on touch input
   */
  private updateJoystick(touchX: number, touchY: number): void {
    if (!this.joystick) return;

    // Calculate distance and angle from center
    const dx = touchX - this.joystick.centerX;
    const dy = touchY - this.joystick.centerY;
    const distance = Math.sqrt(dx * dx + dy * dy);

    // Limit stick movement to joystick radius
    let limitedDistance = Math.min(distance, this.joystick.radius);
    
    // Calculate normalized direction (-1 to 1)
    if (distance > 0) {
      const normalizedX = dx / distance;
      const normalizedY = dy / distance;

      // Update stick visual position
      if (this.joystick.stick) {
        this.joystick.stick.x = normalizedX * limitedDistance;
        this.joystick.stick.y = normalizedY * limitedDistance;
      }

      // Update heading line and angle text when dragging
      // The line should point directly from center to where user is dragging
      // Use the normalized direction directly - no complex angle conversions needed
      
      // Calculate ship angle for display (0° = up, 90° = right, 180° = down, 270° = left)
      // atan2(-normalizedY, normalizedX) because Y is inverted (screen Y increases downward)
      // Adding 90° converts from Phaser's system (0° = right) to ship system (0° = up)
      const angleRad = Math.atan2(-normalizedY, normalizedX); // Invert Y for screen coordinates
      let shipAngle = Phaser.Math.RadToDeg(angleRad) + 90; // Convert to ship angle (0° = up)
      while (shipAngle < 0) shipAngle += 360;
      while (shipAngle >= 360) shipAngle -= 360;

      // Update angle text
      if (this.joystick.angleText) {
        this.joystick.angleText.setText(`${Math.round(shipAngle)}°`);
      }

      // Update heading line to point directly in drag direction
      // Use normalizedX and normalizedY directly - they already point in the right direction
      if (this.joystick.headingLine) {
        const lineLength = this.joystick.radius * 0.9;
        // Simply use the normalized direction vector directly
        // normalizedX and normalizedY already point from center to drag position
        const endX = normalizedX * lineLength;
        const endY = normalizedY * lineLength; // Don't invert - this matches screen coordinates
        this.joystick.headingLine.setTo(0, 0, endX, endY);
      }

      // Update input state (normalized -1 to 1)
      // Invert Y so that pushing up (negative dy) gives positive joystickY
      this.inputState.joystickX = normalizedX;
      this.inputState.joystickY = -normalizedY;
    } else {
      this.inputState.joystickX = 0;
      this.inputState.joystickY = 0;
    }
  }

  /**
   * Reset joystick to center position
   */
  private resetJoystick(): void {
    if (!this.joystick) return;

    this.joystick.active = false;
    this.joystick.pointerId = undefined;

    // Animate stick back to center
    if (this.joystick.stick) {
      this.scene.tweens.add({
        targets: this.joystick.stick,
        x: 0,
        y: 0,
        duration: 100,
        ease: 'Power2',
      });
    }

    // Reset input state
    this.inputState.joystickX = 0;
    this.inputState.joystickY = 0;
  }

  /**
   * Get current input state
   */
  getInputState(): TouchInputState {
    return { ...this.inputState };
  }

  /**
   * Check if speed dial is actively being dragged by user
   */
  isSpeedDialActive(): boolean {
    return this.speedDial?.active === true;
  }

  /**
   * Set speed dial value directly (for keyboard control) - updates both input state and visual
   * @param value - Speed value from -5 to 5
   */
  setSpeedValue(value: number): void {
    // Always update input state when explicitly set via keyboard
    this.inputState.speed = Phaser.Math.Clamp(value, -5, 5);
    this.updateSpeedDialVisual(value, true); // Force immediate update for keyboard
  }

  /**
   * Update speed dial visual only (for reflecting actual velocity) - doesn't change input state if actively dragging
   */
  setSpeed(value: number): void {
    // Only update input state if user isn't actively dragging the speed dial
    if (!this.speedDial?.active) {
      this.inputState.speed = Phaser.Math.Clamp(value, -5, 5);
    }
    this.updateSpeedDialVisual(value, false); // Regular update
  }

  /**
   * Update the visual indicator of the speed dial
   * @param value - Speed value from -5 to 5
   * @param forceImmediate - If true, update immediately without checking thresholds (for keyboard control)
   */
  private updateSpeedDialVisual(value: number, forceImmediate: boolean = false): void {
    // Update visual indicator if speed dial exists
    if (this.speedDial && this.speedDial.indicator) {
      const speedLevels = [5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5];
      const normalizedValue = (value + 5) / 10; // Convert -5 to 5 range to 0 to 1
      // Invert: +5 (speed 5) should be at top (minY), -5 should be at bottom (maxY)
      // So we subtract from maxY instead of adding to minY
      const targetY = this.speedDial.maxY - (normalizedValue * (this.speedDial.maxY - this.speedDial.minY));
      
      // Don't update if user is actively dragging (unless forced)
      if (this.speedDial.active && !forceImmediate) {
        return;
      }
      
      const currentY = this.speedDial.indicator.y;
      
      if (forceImmediate) {
        // For keyboard control, update immediately
        // Kill any existing tween on this indicator first
        this.scene.tweens.killTweensOf(this.speedDial.indicator);
        // Update directly to target position (or use very fast tween)
        this.speedDial.indicator.y = targetY;
      } else if (Math.abs(currentY - targetY) > 1) {
        // Large change, animate smoothly
        this.scene.tweens.killTweensOf(this.speedDial.indicator);
        this.scene.tweens.add({
          targets: this.speedDial.indicator,
          y: targetY,
          duration: 100,
          ease: 'Power2'
        });
      } else {
        // Small change, update directly
        this.speedDial.indicator.y = targetY;
      }
      
      // Highlight the bar at current speed
      speedLevels.forEach((level, index) => {
        if (this.speedDial?.bars && this.speedDial.bars[index]) {
          const roundedValue = Math.round(value);
          if (level === roundedValue) {
            this.speedDial.bars[index].setAlpha(1.0);
            this.speedDial.bars[index].setScale(1.2, 1.0);
          } else {
            this.speedDial.bars[index].setAlpha(0.6);
            this.speedDial.bars[index].setScale(1.0, 1.0);
          }
        }
      });
    }
  }

  /**
   * Update joystick visual position based on ship rotation angle (for keyboard control)
   * Only updates visual, doesn't change input state if user is actively using joystick
   * @param shipAngle - Ship rotation angle in degrees (0 = pointing up)
   */
  setJoystickFromAngle(shipAngle: number): void {
    if (!this.joystick || this.joystick.active) return; // Don't update if user is actively using it

    // Normalize angle to 0-360 range
    let normalizedAngle = shipAngle;
    while (normalizedAngle < 0) normalizedAngle += 360;
    while (normalizedAngle >= 360) normalizedAngle -= 360;

    // Update angle text display
    if (this.joystick.angleText) {
      this.joystick.angleText.setText(`${Math.round(normalizedAngle)}°`);
    }

    // Update heading line to point in ship's direction
    // Ship angle: 0 = up, 90 = right, 180 = down, 270 = left
    // Phaser line: angle 0 = right, 90 = down, so we need to adjust
    // Convert to radians and adjust for Phaser's coordinate system
    const angleRad = Phaser.Math.DegToRad(shipAngle - 90); // Adjust for Phaser's coordinate system
    
    if (this.joystick.headingLine) {
      const lineLength = this.joystick.radius * 0.9;
      const endX = Math.cos(angleRad) * lineLength;
      const endY = Math.sin(angleRad) * lineLength;
      
      // Update line position (from center to end point)
      this.joystick.headingLine.setTo(0, 0, endX, endY);
    }

    // Convert ship angle to joystick direction for stick position
    const shipAngleRad = Phaser.Math.DegToRad(shipAngle - 90);
    
    // Calculate normalized direction
    const normalizedX = Math.cos(shipAngleRad);
    const normalizedY = Math.sin(shipAngleRad);
    
    // Don't update input state - only visual feedback
    // The input state should only come from actual touch input
    
    // Update stick visual position
    if (this.joystick.stick) {
      const limitedDistance = this.joystick.radius * 0.8; // Slightly less than max for visual feedback
      const targetX = normalizedX * limitedDistance;
      const targetY = normalizedY * limitedDistance;
      
      // Check if position changed significantly to avoid unnecessary tweens
      const currentX = this.joystick.stick.x;
      const currentY = this.joystick.stick.y;
      const distance = Math.sqrt((currentX - targetX) ** 2 + (currentY - targetY) ** 2);
      
      if (distance > 2) {
        // Kill any existing tween on this stick
        this.scene.tweens.killTweensOf(this.joystick.stick);
        // Smooth animation to target position
        this.scene.tweens.add({
          targets: this.joystick.stick,
          x: targetX,
          y: targetY,
          duration: 100,
          ease: 'Power2'
        });
      }
    }
  }

  /**
   * Reset joystick visual to center (when no rotation input)
   */
  resetJoystickVisual(): void {
    if (!this.joystick || this.joystick.active) return; // Don't reset if user is actively using it

    if (this.joystick.stick) {
      const currentX = this.joystick.stick.x;
      const currentY = this.joystick.stick.y;
      const distance = Math.sqrt(currentX ** 2 + currentY ** 2);
      
      if (distance > 1) {
        // Kill any existing tween on this stick
        this.scene.tweens.killTweensOf(this.joystick.stick);
        // Animate back to center
        this.scene.tweens.add({
          targets: this.joystick.stick,
          x: 0,
          y: 0,
          duration: 150,
          ease: 'Power2',
        });
      } else {
        // Already close to center, snap to center
        this.joystick.stick.x = 0;
        this.joystick.stick.y = 0;
      }
    }

    // Don't reset input state here - it should only be reset by actual touch input release
  }

  /**
   * Clean up touch controls
   */
  destroy(): void {
    Object.values(this.buttons).forEach(button => {
      if (button) {
        button.destroy();
      }
    });
    this.buttons = {};

    if (this.joystick) {
      if (this.joystick.base) {
        this.joystick.base.destroy();
      }
      if (this.joystick.stick) {
        this.joystick.stick.destroy();
      }
      this.joystick = undefined;
    }

    // Reset input state
    this.inputState = {
      rotateLeft: false,
      rotateRight: false,
      moveForward: false,
      moveBackward: false,
      shooting: false,
      accelerate: false,
      speed: 0,
      joystickX: 0,
      joystickY: 0,
    };
  }

  /**
   * Create a speed dial control (bar chart style, draggable)
   */
  private createSpeedDial(centerX: number, centerY: number, width: number, height: number): void {
    const container = this.scene.add.container(centerX, centerY);
    container.setDepth(100);
    // No rotation - keep it straight

    // Background panel
    const background = this.scene.add.rectangle(0, 0, width + 10, height + 20, 0x000511, 0.9);
    background.setStrokeStyle(2, 0x00ff00, 0.8);
    container.add(background);

    // Create bars for each speed level (5 to -5)
    const speedLevels = [5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5];
    const barCount = speedLevels.length;
    const barSpacing = height / (barCount + 1);
    const barWidth = width * 0.6;
    const bars: Phaser.GameObjects.Rectangle[] = [];

    speedLevels.forEach((level, index) => {
      const barY = -height / 2 + (index + 1) * barSpacing;
      const barHeight = barSpacing * 0.6;
      
      // Color based on speed direction
      let barColor = 0x00ff00; // Green for forward
      if (level < 0) {
        barColor = 0xff0000; // Red for reverse
      } else if (level === 0) {
        barColor = 0x888888; // Grey for neutral
      }

      const bar = this.scene.add.rectangle(0, barY, barWidth, barHeight, barColor, 0.6);
      bar.setStrokeStyle(1, barColor, 0.8);
      container.add(bar);
      bars.push(bar);

      // Speed label
      const labelText = this.scene.add.text(
        width / 2 - 8,
        barY,
        level > 0 ? `+${level}` : `${level}`,
        {
          fontSize: '10px',
          color: '#00ff00',
          fontFamily: 'Arial',
          fontStyle: 'bold',
          stroke: '#000000',
          strokeThickness: 1,
        }
      );
      labelText.setOrigin(1, 0.5);
      container.add(labelText);
    });

    // Indicator line (draggable)
    const indicator = this.scene.add.rectangle(0, 0, width, 3, 0xffff00, 1.0);
    indicator.setStrokeStyle(2, 0xffff00, 1.0);
    container.add(indicator);

    // Make container interactive
    container.setSize(width + 10, height + 20);
    container.setInteractive({ useHandCursor: true });

    // Store speed dial state
    const minY = -height / 2 + barSpacing;
    const maxY = height / 2 - barSpacing;
    this.speedDial = {
      container,
      background,
      bars,
      indicator,
      centerX,
      centerY,
      width,
      height,
      active: false,
      minY,
      maxY,
    };

    // Handle pointer events
    container.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
      this.speedDial!.active = true;
      this.speedDial!.pointerId = pointer.id;
      this.updateSpeedDial(pointer.y);
    });

    this.scene.input.on('pointermove', (pointer: Phaser.Input.Pointer) => {
      if (this.speedDial?.active && this.speedDial.pointerId === pointer.id) {
        this.updateSpeedDial(pointer.y);
      }
    });

    this.scene.input.on('pointerup', (pointer: Phaser.Input.Pointer) => {
      if (this.speedDial?.active && this.speedDial.pointerId === pointer.id) {
        this.speedDial.active = false;
        this.speedDial.pointerId = undefined;
      }
    });
  }

  /**
   * Update speed dial position based on touch input
   */
  private updateSpeedDial(touchY: number): void {
    if (!this.speedDial) return;

    // Convert screen Y to container-relative Y
    const containerY = touchY - this.speedDial.centerY;
    
    // Clamp to bounds
    let clampedY = Phaser.Math.Clamp(containerY, this.speedDial.minY, this.speedDial.maxY);
    
    // Update indicator position
    if (this.speedDial.indicator) {
      this.speedDial.indicator.y = clampedY;
    }

    // Calculate speed value (-5 to 5)
    const normalizedY = (clampedY - this.speedDial.minY) / (this.speedDial.maxY - this.speedDial.minY);
    const speed = Math.round(5 - (normalizedY * 10)); // 5 at top, -5 at bottom

    // Update input state
    this.inputState.speed = speed;

    // Highlight the bar at current speed
    const speedLevels = [5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5];
    speedLevels.forEach((level, index) => {
      if (this.speedDial?.bars && this.speedDial.bars[index]) {
        if (level === speed) {
          this.speedDial.bars[index].setAlpha(1.0);
          this.speedDial.bars[index].setScale(1.2, 1.0);
        } else {
          this.speedDial.bars[index].setAlpha(0.6);
          this.speedDial.bars[index].setScale(1.0, 1.0);
        }
      }
    });
  }
}


