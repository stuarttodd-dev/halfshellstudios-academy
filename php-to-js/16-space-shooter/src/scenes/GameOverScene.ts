import Phaser from 'phaser';
import { SCENE_KEYS } from '../config';
import { isMobileDevice } from '../utils/TouchControls';
import { saveScore, isHighScore } from '../utils/ScoreManager';
import { scaleSize } from '../utils/ResponsiveScale';

/**
 * GameOverScene - Shown when the player dies
 */
export class GameOverScene extends Phaser.Scene {
  private finalScore: number = 0;
  private nameInput?: Phaser.GameObjects.DOMElement;
  private playerName: string = '';
  private nameSubmitted: boolean = false;

  constructor() {
    super({ key: SCENE_KEYS.GAME_OVER });
  }

  /**
   * Preload the background image
   */
  preload() {
    // Assets already loaded in LoadingScene
  }

  init(data: { score?: number }) {
    // Get score from previous scene
    this.finalScore = data.score || 0;
    this.nameSubmitted = false;
    this.playerName = '';
  }

  create() {
    const { width, height } = this.scale;

    // Stop game scene background music
    try {
      const gameSceneMusic = this.sound.get('gamescene');
      if (gameSceneMusic && gameSceneMusic.isPlaying) {
        gameSceneMusic.stop();
        console.log('🛑 Game scene music stopped');
      }
      // Don't play any music in game over scene
    } catch (e) {
      // Ignore errors
    }

    // Create background using the loaded image
    if (this.textures.exists('gameOverBackground')) {
      const bg = this.add.image(width / 2, height / 2, 'gameOverBackground');
      bg.setDisplaySize(width, height); // Scale to fit screen
      bg.setDepth(-1); // Behind everything
    } else {
      // Fallback to solid color if image didn't load
      this.add.rectangle(width / 2, height / 2, width, height, 0x000033);
    }

    // Add game over text with black background
    const gameOverStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 48)}px`,
      color: '#ff0000',
      fontFamily: 'Arial',
      fontStyle: 'bold',
      backgroundColor: '#000000',
      padding: { x: 20, y: 10 },
    };

    const gameOverText = this.add.text(width / 2, height / 2 - scaleSize(this, 200), 'GAME OVER', gameOverStyle);
    gameOverText.setOrigin(0.5);
    gameOverText.setScale(1, 1); // Prevent text distortion

    // Add final score with black background
    const scoreStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 36)}px`,
      color: '#ffff00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
      backgroundColor: '#000000',
      padding: { x: 20, y: 10 },
    };

    const scoreText = this.add.text(
      width / 2,
      height / 2 - scaleSize(this, 120),
      `Humans Evacuated: ${this.finalScore.toLocaleString()}`,
      scoreStyle
    );
    scoreText.setOrigin(0.5);
    scoreText.setScale(1, 1); // Prevent text distortion

    // Check if this is a high score and prompt for name
    if (isHighScore(this.finalScore)) {
      this.showNameInput(width, height);
    } else {
      this.showRestartOption(width, height);
    }
  }

  /**
   * Show name input for high score entry
   */
  private showNameInput(width: number, height: number): void {
    const instructionStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 28)}px`,
      color: '#00ff00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
      backgroundColor: '#000000',
      padding: { x: 15, y: 8 },
    };

    const highScoreText = this.add.text(
      width / 2,
      height / 2 - scaleSize(this, 30),
      'NEW HIGH SCORE!',
      instructionStyle
    );
    highScoreText.setOrigin(0.5);
    highScoreText.setScale(1, 1);

    // Create HTML input for name entry
    const inputElement = document.createElement('input');
    inputElement.type = 'text';
    inputElement.style.position = 'absolute';
    inputElement.style.width = `${scaleSize(this, 200)}px`;
    inputElement.style.height = `${scaleSize(this, 40)}px`;
    inputElement.style.fontSize = `${scaleSize(this, 24)}px`;
    inputElement.style.textAlign = 'center';
    inputElement.style.textTransform = 'uppercase';
    inputElement.style.border = '2px solid #00ff00';
    inputElement.style.backgroundColor = '#000000';
    inputElement.style.color = '#00ff00';
    inputElement.style.fontFamily = 'Arial, monospace';
    inputElement.minLength = 5;
    inputElement.maxLength = 5;
    inputElement.placeholder = 'NAME';
    inputElement.autofocus = true;
    inputElement.required = true;

    // Only allow alphanumeric characters
    inputElement.addEventListener('input', (e: Event) => {
      const target = e.target as HTMLInputElement;
      target.value = target.value.replace(/[^A-Z0-9]/gi, '').toUpperCase();
    });

    // Center the input (moved 60px lower)
    const inputX = width / 2;
    const inputY = height / 2 + scaleSize(this, 30) + 60;

    this.nameInput = this.add.dom(inputX, inputY, inputElement);
    this.nameInput.setOrigin(0.5);

    // Add submit instruction
    const submitStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 20)}px`,
      color: '#ffffff',
      fontFamily: 'Arial',
      backgroundColor: '#000000',
      padding: { x: 10, y: 5 },
    };

    const submitText = this.add.text(
      width / 2,
      height / 2 + scaleSize(this, 90) + 60, // Moved 60px lower
      isMobileDevice() ? 'Tap to Submit' : 'Press ENTER to Submit',
      submitStyle
    );
    submitText.setOrigin(0.5);
    submitText.setScale(1, 1);

    // Add helper text below input
    const helperStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 16)}px`,
      color: '#888888',
      fontFamily: 'Arial',
      backgroundColor: '#000000',
      padding: { x: 10, y: 5 },
    };
    const helperText = this.add.text(
      width / 2,
      height / 2 + scaleSize(this, 55),
      'Enter exactly 5 characters',
      helperStyle
    );
    helperText.setOrigin(0.5);
    helperText.setScale(1, 1);

    // Error message (hidden by default)
    const errorStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 18)}px`,
      color: '#ff0000',
      fontFamily: 'Arial',
      backgroundColor: '#000000',
      padding: { x: 10, y: 5 },
    };
    const errorText = this.add.text(
      width / 2,
      height / 2 + scaleSize(this, 70),
      'Please enter exactly 5 characters',
      errorStyle
    );
    errorText.setOrigin(0.5);
    errorText.setScale(1, 1);
    errorText.setVisible(false);

    // Update submit text color based on input length
    const updateSubmitButton = () => {
      const isValid = inputElement.value.length === 5;
      submitText.setStyle({ 
        color: isValid ? '#00ff00' : '#888888',
      });
    };

    // Update on input
    inputElement.addEventListener('input', updateSubmitButton);

    // Submit handler function
    const attemptSubmit = () => {
      if (this.nameSubmitted) return;
      
      const inputValue = inputElement.value.trim();
      if (inputValue.length === 5) {
        // Hide error, submit
        errorText.setVisible(false);
        this.submitName(inputValue);
      } else {
        // Show error message
        errorText.setVisible(true);
        // Hide error after 3 seconds
        this.time.delayedCall(3000, () => {
          errorText.setVisible(false);
        });
      }
    };

    // Make submit text interactive on mobile
    if (isMobileDevice()) {
      submitText.setInteractive({ useHandCursor: true });
      submitText.on('pointerdown', () => {
        attemptSubmit();
      });
    }

    // Handle Enter key
    this.input.keyboard?.on('keydown-ENTER', () => {
      attemptSubmit();
    });

    // Handle input focus and enter key on the input element
    inputElement.addEventListener('keydown', (e: KeyboardEvent) => {
      if (e.key === 'Enter' && !this.nameSubmitted) {
        e.preventDefault();
        attemptSubmit();
      }
    });

    // Initial state
    updateSubmitButton();
  }

  /**
   * Submit the player name and save score
   */
  private submitName(name: string): void {
    if (this.nameSubmitted) return;

    // Validate exactly 5 characters
    const trimmedName = name.trim().toUpperCase();
    if (trimmedName.length !== 5) {
      return; // Don't submit if not exactly 5 characters
    }

    this.nameSubmitted = true;
    this.playerName = trimmedName;

    // Save the score (synchronous now)
    saveScore(this.playerName, this.finalScore);

    // Remove input
    if (this.nameInput) {
      this.nameInput.destroy();
      this.nameInput = undefined;
    }

    // Return to start scene after a brief delay
    this.time.delayedCall(500, () => {
      this.scene.start(SCENE_KEYS.START);
    });
  }

  /**
   * Show restart option
   */
  private showRestartOption(width: number, height: number): void {
    // Add restart instructions with black background
    const instructionStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this, 24)}px`,
      color: '#ffffff',
      fontFamily: 'Arial',
      backgroundColor: '#000000',
      padding: { x: 15, y: 8 },
    };

    // Show different instructions based on device type
    const restartText = isMobileDevice() ? 'Tap to Restart' : 'Press SPACE to Restart';
    
    const instructions = this.add.text(
      width / 2,
      height / 2 + scaleSize(this, 150),
      restartText,
      instructionStyle
    );
    instructions.setOrigin(0.5);
    instructions.setScale(1, 1); // Prevent text distortion

    // Flash the restart text
    this.tweens.add({
      targets: instructions,
      alpha: { from: 1, to: 0.3 },
      duration: 800,
      yoyo: true,
      repeat: -1,
      ease: 'Sine.easeInOut'
    });

    // Listen for spacebar to restart
    this.input.keyboard?.once('keydown-SPACE', () => {
      this.scene.start(SCENE_KEYS.GAME);
    });

    // Also allow clicking to restart
    this.input.once('pointerdown', () => {
      this.scene.start(SCENE_KEYS.GAME);
    });
  }
}

