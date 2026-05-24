import Phaser from 'phaser';
import { SCENE_KEYS } from '../config';
import { isMobileDevice } from '../utils/TouchControls';
import { scaleSize } from '../utils/ResponsiveScale';
import { getScores } from '../utils/ScoreManager';

/**
 * StartScene - The game's start screen
 * This is the first scene players see when the game loads
 * 
 * Key concepts:
 * - Phaser Scenes are like different "screens" or "states" in your game
 * - create() is called once when the scene starts
 * - This is where we set up UI elements, text, buttons, etc.
 */
export class StartScene extends Phaser.Scene {
  private backgroundMusic?: Phaser.Sound.BaseSound;
  private showingScores: boolean = false;
  private scoreboardContainer?: Phaser.GameObjects.Container;

  constructor() {
    super({ key: SCENE_KEYS.START });
  }

  /**
   * Preload - Assets are now loaded in LoadingScene, so this is empty
   * Assets are shared across scenes, so they remain available once loaded
   */
  preload() {
    // Assets are already loaded in LoadingScene
    // Log when sounds are available
    this.load.on('filecomplete-audio-background', () => {
      console.log('✅ Background music loaded successfully');
    });
    
    this.load.on('filecomplete', (key: string, type: string) => {
      if (type === 'audio') {
        console.log(`✅ Audio loaded: ${key}`);
      }
    });
  }

  /**
   * Called once when the scene is created
   * This is where we add all our start screen elements
   */
  create() {
    const { width, height } = this.scale;

    // Create background using the loaded image
    if (this.textures.exists('startBackground')) {
      const bg = this.add.image(width / 2, height / 2, 'startBackground');
      bg.setDisplaySize(width, height); // Scale to fit screen
      bg.setDepth(-1);
      console.log('✅ Start background image displayed');
    } else {
      // Fallback to solid color if image didn't load
      this.add.rectangle(width / 2, height / 2, width, height, 0x000033);
      console.log('⚠️ Using fallback background color');
    }

    // Add instructions - only show keyboard controls if NOT on mobile
    const baseFontSize = 24;
    const responsiveFontSize = `${Math.round(scaleSize(this, baseFontSize))}px`;
    const instructionStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: responsiveFontSize,
      color: '#ffffff',
      fontFamily: 'Arial',
    };

    let instructionText: string;
    
    if (isMobileDevice()) {
      // Mobile: Show tap instruction
      instructionText = 'Tap to Start';
    } else {
      // Desktop: Show keyboard instructions
      instructionText = 'Press SPACE to Start\n\nArrow Keys to Move\nSPACE to Shoot';
    }

    const instructions = this.add.text(
      width / 2,
      height - scaleSize(this, 100),
      instructionText,
      instructionStyle
    );
    instructions.setOrigin(0.5);
    instructions.setDepth(1); // Above background
    instructions.setScale(1, 1); // Prevent text distortion on mobile
    
    // Make the text flash/blink
    this.tweens.add({
      targets: instructions,
      alpha: { from: 1, to: 0.3 },
      duration: 800,
      yoyo: true,
      repeat: -1, // Repeat forever
      ease: 'Sine.easeInOut'
    });

    // Add scoreboard button (store reference to check clicks)
    let scoreButton: Phaser.GameObjects.Text | null = null;
    this.createScoreboardButton(width, height, (button) => {
      scoreButton = button;
    });

    // Add Half Shell Studios logo at top right
    let logoImage: Phaser.GameObjects.Image | null = null;
    
    if (this.textures.exists('halfShellLogo')) {
      // Use the logo image if loaded - position at top right
      logoImage = this.add.image(width - 20, 20, 'halfShellLogo');
      logoImage.setOrigin(1, 0); // Right-aligned, top-aligned
      
      // Scale logo responsively (base max width 150px, scale with screen)
      const baseMaxWidth = 150;
      const baseMinWidth = 100;
      const maxWidth = scaleSize(this, baseMaxWidth);
      const minWidth = scaleSize(this, baseMinWidth);
      const logoWidth = logoImage.width;
      if (logoWidth > maxWidth) {
        const scale = maxWidth / logoWidth;
        logoImage.setScale(scale);
      } else {
        // Scale up if too small
        if (logoWidth < minWidth) {
          const scale = minWidth / logoWidth;
          logoImage.setScale(scale);
        }
      }
      
      logoImage.setDepth(10); // Above everything
      logoImage.setInteractive({ useHandCursor: true });
      
      // Add subtle hover effect
      logoImage.on('pointerover', () => {
        this.tweens.add({
          targets: logoImage,
          scale: { from: logoImage!.scaleX, to: logoImage!.scaleX * 1.1 },
          duration: 200,
          ease: 'Power2'
        });
      });
      
      logoImage.on('pointerout', () => {
        this.tweens.add({
          targets: logoImage,
          scale: { from: logoImage!.scaleX, to: logoImage!.scaleX / 1.1 },
          duration: 200,
          ease: 'Power2'
        });
      });

      // Open Half Shell Studios website when clicked
      logoImage.on('pointerdown', () => {
        window.open('https://halfshellstudios.co.uk', '_blank');
      });
    }

    // Unlock audio context (required by browsers for autoplay)
    if (this.sound && this.sound.locked) {
      this.sound.unlock();
    }
    
    // Start background music after a small delay to ensure it's loaded
    this.time.delayedCall(100, () => {
      try {
        // Stop any game scene music first
        const gameSceneMusic = this.sound.get('gamescene');
        if (gameSceneMusic && gameSceneMusic.isPlaying) {
          gameSceneMusic.stop();
          console.log('🛑 Game scene music stopped in start scene');
        }
        
        // Check if sound exists in cache
        if (this.cache.audio.exists('background')) {
          // Check if music is already playing (might be from a previous visit to this scene)
          const existingMusic = this.sound.get('background');
          if (!existingMusic || !existingMusic.isPlaying) {
            this.backgroundMusic = this.sound.add('background', { loop: true, volume: 0.5 });
            this.backgroundMusic.play();
            console.log('✅ Background music started on start scene');
          } else {
            this.backgroundMusic = existingMusic as Phaser.Sound.BaseSound;
            console.log('✅ Background music already playing');
          }
        } else {
          console.warn('⚠️ Background sound not found in cache');
          console.log('Available sounds:', this.cache.audio.getKeys());
        }
      } catch (e) {
        console.error('Error playing background music:', e);
      }
    });

    // Helper function to start game (goes to intro scene first)
    const startGame = () => {
      this.scene.start(SCENE_KEYS.INTRO);
    };

    // Setup keyboard handlers
    this.input.keyboard?.on('keydown-SPACE', () => {
      // Don't start game if scoreboard is open
      if (!this.showingScores) {
        startGame();
      }
    });

    // Setup tap/click handlers
    this.setupTapHandler(logoImage, scoreButton, startGame, width, height);
  }

  /**
   * Create scoreboard button and toggle functionality
   */
  private createScoreboardButton(width: number, height: number, onCreated?: (button: Phaser.GameObjects.Text) => void): void {
    const buttonStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 20)}px`,
      color: '#ffff00',
      fontFamily: 'Arial',
      backgroundColor: '#000000',
      padding: { x: 10, y: 5 },
    };

    const margin = 20;
    const scoreButton = this.add.text(
      margin,
      scaleSize(this, 20),
      'HIGH SCORES',
      buttonStyle
    );
    scoreButton.setOrigin(0, 0); // Left-aligned, top-aligned
    scoreButton.setDepth(5);
    scoreButton.setInteractive({ useHandCursor: true });

    // Add hover effect
    scoreButton.on('pointerover', () => {
      scoreButton.setStyle({ color: '#00ff00' });
    });
    scoreButton.on('pointerout', () => {
      scoreButton.setStyle({ color: '#ffff00' });
    });

    // Toggle scoreboard on click - handle both pointerdown and pointerup to prevent game start
    scoreButton.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
      pointer.event.stopPropagation(); // Prevent event from bubbling to scene
      pointer.event.preventDefault();
    });
    
    scoreButton.on('pointerup', (pointer: Phaser.Input.Pointer) => {
      pointer.event.stopPropagation();
      pointer.event.preventDefault();
      this.toggleScoreboard(width, height);
    });

    // Also allow 'S' key to toggle scoreboard, and 'X' to close
    this.input.keyboard?.on('keydown-S', () => {
      if (!this.showingScores) {
        this.toggleScoreboard(width, height);
      }
    });
    this.input.keyboard?.on('keydown-X', () => {
      if (this.showingScores) {
        this.toggleScoreboard(width, height);
      }
    });

    // Call callback with button reference
    if (onCreated) {
      onCreated(scoreButton);
    }
  }

  /**
   * Setup tap handler - different behavior based on scoreboard state
   * Only enabled on mobile devices
   */
  private setupTapHandler(
    logoImage: Phaser.GameObjects.Image | null,
    scoreButton: Phaser.GameObjects.Text | null,
    startGame: () => void,
    width: number,
    height: number
  ): void {
    // Only enable tap handler on mobile devices
    if (!isMobileDevice()) {
      return;
    }

    // Single tap handler that behaves differently based on scoreboard state
    this.input.on('pointerup', (pointer: Phaser.Input.Pointer) => {
      // If scoreboard is open, tap closes it
      if (this.showingScores) {
        if (this.scoreboardContainer) {
          // On mobile, any tap closes the scoreboard
          this.toggleScoreboard(width, height);
        }
        return; // Don't start game if scoreboard is open
      }

      // Scoreboard is closed - check if we should start game
      // Don't start game if clicking on logo
      if (logoImage) {
        const logoBounds = logoImage.getBounds();
        if (logoBounds.contains(pointer.x, pointer.y)) {
          return;
        }
      }

      // Don't start game if clicking on score button
      if (scoreButton) {
        const buttonBounds = scoreButton.getBounds();
        if (buttonBounds.contains(pointer.x, pointer.y)) {
          return;
        }
      }

      // Start game (scoreboard is closed, not clicking on logo/button)
      startGame();
    });
  }

  /**
   * Toggle scoreboard visibility
   */
  private toggleScoreboard(width: number, height: number): void {
    if (this.showingScores) {
      // Hide scoreboard
      if (this.scoreboardContainer) {
        this.scoreboardContainer.destroy();
        this.scoreboardContainer = undefined;
      }
      this.showingScores = false;
    } else {
      // Show scoreboard (synchronous now)
      this.showScoreboard(width, height);
      this.showingScores = true;
    }
  }


  /**
   * Display the scoreboard
   */
  private showScoreboard(width: number, height: number): void {
    // Remove existing scoreboard if any
    if (this.scoreboardContainer) {
      this.scoreboardContainer.destroy();
    }

    const scores = getScores();
    const container = this.add.container(width / 2, height / 2);
    container.setDepth(10);

    // Background (made taller to accommodate larger text)
    const bg = this.add.rectangle(0, 0, scaleSize(this, 550), scaleSize(this, 480), 0x000000, 0.9);
    bg.setStrokeStyle(2, 0xffff00, 1);
    container.add(bg);

    // Title
    const titleStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 32)}px`,
      color: '#ffff00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
    };
    const title = this.add.text(0, -scaleSize(this, 170), 'HIGH SCORES', titleStyle);
    title.setOrigin(0.5);
    container.add(title);

    // Display scores
    const entryStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 24)}px`, // Increased from 18px
      color: '#ffffff',
      fontFamily: 'Arial, monospace',
    };

    if (scores.length === 0) {
      const noScores = this.add.text(0, 0, 'No scores yet!\nBe the first!', {
        ...entryStyle,
        color: '#888888',
        fontSize: `${scaleSize(this, 28)}px`, // Larger for empty state
      });
      noScores.setOrigin(0.5);
      container.add(noScores);
    } else {
      const startY = -scaleSize(this, 100);
      const lineHeight = scaleSize(this, 40); // Increased from 30px to accommodate larger text
      
      // Header
      const headerStyle: Phaser.Types.GameObjects.Text.TextStyle = {
        ...entryStyle,
        color: '#ffff00',
        fontStyle: 'bold',
        fontSize: `${scaleSize(this, 26)}px`, // Slightly larger header
      };
      const header = this.add.text(0, startY, 'RANK  NAME   SCORE', headerStyle);
      header.setOrigin(0.5);
      container.add(header);

      // Score entries
      scores.forEach((entry, index) => {
        const y = startY + (index + 1) * lineHeight;
        const rank = (index + 1).toString().padStart(2, ' ');
        const name = entry.name.padEnd(5, ' ');
        const score = entry.score.toLocaleString().padStart(10, ' ');
        const text = this.add.text(0, y, `${rank}. ${name} ${score}`, entryStyle);
        text.setOrigin(0.5);
        container.add(text);
      });
    }

    // Close instruction
    const closeStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 14)}px`,
      color: '#888888',
      fontFamily: 'Arial',
    };
    const closeText = this.add.text(
      0,
      scaleSize(this, 170),
      isMobileDevice() ? 'Tap to Close' : 'Press X to Close',
      closeStyle
    );
    closeText.setOrigin(0.5);
    container.add(closeText);

    // Make the entire container interactive to catch all clicks
    container.setInteractive(new Phaser.Geom.Rectangle(
      -scaleSize(this, 275),
      -scaleSize(this, 240),
      scaleSize(this, 550),
      scaleSize(this, 480)
    ), Phaser.Geom.Rectangle.Contains);

    // Make background clickable to close
    bg.setInteractive({ useHandCursor: true });
    bg.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
      pointer.event.stopPropagation(); // Prevent event from bubbling to scene
      this.toggleScoreboard(width, height);
    });

    // Prevent all pointer events on the container from starting the game
    container.on('pointerdown', (pointer: Phaser.Input.Pointer) => {
      pointer.event.stopPropagation();
    });
    container.on('pointerup', (pointer: Phaser.Input.Pointer) => {
      pointer.event.stopPropagation();
    });
    container.on('pointermove', (pointer: Phaser.Input.Pointer) => {
      pointer.event.stopPropagation();
    });

    this.scoreboardContainer = container;
  }
}

