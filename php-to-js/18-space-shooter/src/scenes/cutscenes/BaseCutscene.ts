import Phaser from 'phaser';

/**
 * Cutscene transition configuration
 * Each transition defines a background image, text, and how long to show it
 */
export interface CutsceneTransition {
  backgroundKey: string; // Texture key for the background image
  text?: string; // Optional text to display (renders slowly as if being spoken)
  duration: number; // Time in milliseconds to show this background (includes text typing time)
  typingSpeed?: number; // Characters per second for typing effect (default: 30)
}

/**
 * BaseCutscene - Abstract base class for all cutscenes
 * 
 * A cutscene is a Phaser Scene that automatically transitions through
 * a series of backgrounds with timing, plays background music, and
 * exits to a defined scene when complete.
 */
export abstract class BaseCutscene extends Phaser.Scene {
  protected transitions: CutsceneTransition[] = [];
  protected musicKey?: string; // Background music sound key
  protected exitSceneKey: string = ''; // Scene to transition to when complete
  protected backgroundMusic?: Phaser.Sound.BaseSound;
  protected currentTransitionIndex: number = 0;
  protected currentBackground?: Phaser.GameObjects.Image;
  protected currentText?: Phaser.GameObjects.Text;
  protected textTypingTimer?: Phaser.Time.TimerEvent;
  protected isComplete: boolean = false;

  /**
   * Get the transition array - must be implemented by subclasses
   */
  protected abstract getTransitions(): CutsceneTransition[];

  /**
   * Get the background music key - must be implemented by subclasses
   */
  protected abstract getMusicKey(): string | undefined;

  /**
   * Get the exit scene key - must be implemented by subclasses
   */
  protected abstract getExitSceneKey(): string;

  /**
   * Called when the cutscene starts
   */
  create(): void {
    // Get configuration from subclass
    this.transitions = this.getTransitions();
    this.musicKey = this.getMusicKey();
    this.exitSceneKey = this.getExitSceneKey();

    // Stop any other background music
    this.stopOtherMusic();

    // Start background music if specified
    if (this.musicKey && this.cache.audio.exists(this.musicKey)) {
      try {
        this.backgroundMusic = this.sound.add(this.musicKey, { loop: true, volume: 0.5 });
        this.backgroundMusic.play();
        console.log(`✅ Cutscene music started: ${this.musicKey}`);
      } catch (e) {
        console.error(`Error playing cutscene music: ${e}`);
      }
    }

    // Start the first transition
    this.startTransition(0);

    // Allow skipping with spacebar or click
    this.setupSkipControls();
  }

  /**
   * Stop other background music that might be playing
   */
  private stopOtherMusic(): void {
    try {
      const backgroundMusic = this.sound.get('background');
      if (backgroundMusic && backgroundMusic.isPlaying) {
        backgroundMusic.stop();
      }
      const gameSceneMusic = this.sound.get('gamescene');
      if (gameSceneMusic && gameSceneMusic.isPlaying) {
        gameSceneMusic.stop();
      }
    } catch (e) {
      // Ignore errors
    }
  }

  /**
   * Start a specific transition with fade effects
   */
  private startTransition(index: number): void {
    if (index >= this.transitions.length) {
      // All transitions complete, exit to next scene
      this.completeCutscene();
      return;
    }

    const transition = this.transitions[index];
    this.currentTransitionIndex = index;

    // Cancel any existing text typing
    if (this.textTypingTimer) {
      this.textTypingTimer.remove();
      this.textTypingTimer = undefined;
    }

    // Fade out previous background and text
    if (this.currentBackground || this.currentText) {
      const fadeOutDuration = 500; // 500ms fade out
      
      if (this.currentBackground) {
        this.tweens.add({
          targets: this.currentBackground,
          alpha: 0,
          duration: fadeOutDuration,
          ease: 'Power2',
          onComplete: () => {
            if (this.currentBackground) {
              this.currentBackground.destroy();
              this.currentBackground = undefined;
            }
            // After fade out, fade in new background
            this.fadeInNewTransition(transition, fadeOutDuration);
          },
        });
      } else {
        // No previous background, just fade in new one
        this.fadeInNewTransition(transition, 0);
      }

      // Fade out text if exists
      if (this.currentText) {
        this.tweens.add({
          targets: this.currentText,
          alpha: 0,
          duration: fadeOutDuration,
          ease: 'Power2',
          onComplete: () => {
            if (this.currentText) {
              this.currentText.destroy();
              this.currentText = undefined;
            }
          },
        });
      }
    } else {
      // No previous transition, just fade in new one
      this.fadeInNewTransition(transition, 0);
    }
  }

  /**
   * Fade in a new transition
   */
  private fadeInNewTransition(transition: CutsceneTransition, delay: number = 0): void {
    const { width, height } = this.scale;
    const fadeInDuration = 500; // 500ms fade in

    // Create new background (starts invisible)
    this.currentBackground = this.add.image(width / 2, height / 2, transition.backgroundKey);
    this.currentBackground.setDisplaySize(width, height);
    this.currentBackground.setDepth(-1);
    this.currentBackground.setAlpha(0);

    // Calculate typing duration if text is provided
    let typingDuration = 0;
    if (transition.text) {
      const charsPerSecond = transition.typingSpeed || 30;
      const charCount = transition.text.length;
      typingDuration = (charCount / charsPerSecond) * 1000; // Convert to milliseconds
    }

    // Fade in the new background
    this.tweens.add({
      targets: this.currentBackground,
      alpha: 1,
      duration: fadeInDuration,
      delay: delay,
      ease: 'Power2',
      onComplete: () => {
        // After fade in complete, start displaying text if provided
        if (transition.text) {
          this.showTypingText(transition.text, transition.typingSpeed || 30);
        }

        // Calculate when to transition to next slide
        // The duration should be: fadeIn + typing duration (text finishes at end)
        // Account for any delay from previous fade out
        const fadeOutDuration = delay > 0 ? 500 : 0; // Previous fade out time
        const timeUntilNext = transition.duration - fadeOutDuration - fadeInDuration;
        
        // Ensure typing finishes at the end (or slightly before)
        const timeAfterFade = Math.max(0, timeUntilNext);
        
        // Schedule next transition so text finishes typing right before transition
        if (transition.text) {
          // Wait until typing should complete (at the end of duration)
          const waitTime = Math.max(0, timeAfterFade - typingDuration + 100); // Small buffer
          this.time.delayedCall(waitTime + typingDuration, () => {
            this.startTransition(this.currentTransitionIndex + 1);
          });
        } else {
          // No text, just wait the full duration
          this.time.delayedCall(timeAfterFade, () => {
            this.startTransition(this.currentTransitionIndex + 1);
          });
        }
      },
    });
  }

  /**
   * Show text with line-by-line flash effect
   */
  private showTypingText(text: string, _typingSpeed: number = 30): void {
    const { width, height } = this.scale;

    // Split text into lines (by \n)
    const lines = text.split('\n');
    
    // Calculate delay between lines (use a slower delay for fade effect)
    const delayBetweenLines = 800; // 800ms between each line fade
    
    // Noir sci-fi style
    const textStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: '28px',
      color: '#e0e0e0', // Slightly off-white for noir feel
      fontFamily: '"Courier New", Courier, monospace', // Monospace for sci-fi/tech feel
      align: 'center',
      wordWrap: { width: width - 100 },
      stroke: '#000000',
      strokeThickness: 3,
      fontStyle: 'normal',
      letterSpacing: 1, // Slightly spaced letters for tech feel
    };

    // Create text object
    this.currentText = this.add.text(width / 2, height / 2 + 150, '', textStyle);
    this.currentText.setOrigin(0.5, 0.5);
    this.currentText.setDepth(100);
    this.currentText.setAlpha(0); // Start invisible

    // Fade in each line one by one
    let currentLineIndex = 0;
    let displayedText = '';

    const fadeInNextLine = () => {
      if (currentLineIndex >= lines.length || this.isComplete) {
        // All lines displayed
        return;
      }

      // Add next line
      if (currentLineIndex > 0) {
        displayedText += '\n';
      }
      displayedText += lines[currentLineIndex];
      currentLineIndex++;

      // Update text and fade it in slowly
      if (this.currentText) {
        this.currentText.setText(displayedText);
        
        // Fade in effect: slow fade from transparent to visible
        const previousAlpha = currentLineIndex > 1 ? 1 : 0; // Keep previous lines visible
        this.currentText.setAlpha(previousAlpha);
        
        // Calculate the new alpha value (accumulate opacity as lines are added)
        // Each new line fades from the current alpha to 1
        const fadeDuration = 1200; // 1.2 seconds for slow fade
        
        this.tweens.add({
          targets: this.currentText,
          alpha: 1,
          duration: fadeDuration,
          ease: 'Power2',
        });

        // Schedule next line fade
        if (currentLineIndex < lines.length) {
          this.textTypingTimer = this.time.delayedCall(delayBetweenLines, fadeInNextLine);
        }
      }
    };

    // Start fading in lines
    fadeInNextLine();
  }

  /**
   * Complete the cutscene and transition to exit scene
   */
  private completeCutscene(): void {
    if (this.isComplete) return;
    this.isComplete = true;

    // Stop background music
    if (this.backgroundMusic && this.backgroundMusic.isPlaying) {
      this.backgroundMusic.stop();
    }

    // Transition to exit scene
    console.log(`🎬 Cutscene complete, transitioning to: ${this.exitSceneKey}`);
    this.scene.start(this.exitSceneKey);
  }

  /**
   * Setup skip controls (spacebar or click)
   */
  private setupSkipControls(): void {
    const { width, height } = this.scale;
    
    // Skip text
    const skipText = this.add.text(
      width / 2,
      height - 30,
      'Press SPACE or Click to Skip',
      {
        fontSize: '18px',
        color: '#ffffff',
        fontFamily: 'Arial',
      }
    );
    skipText.setOrigin(0.5);
    skipText.setAlpha(0.7); // Set alpha after creation
    skipText.setDepth(100);

    // Skip functionality
    const skip = () => {
      this.completeCutscene();
    };

    this.input.keyboard?.once('keydown-SPACE', skip);
    this.input.once('pointerdown', skip);
  }

  /**
   * Cleanup when scene is shutdown
   */
  shutdown(): void {
    if (this.backgroundMusic && this.backgroundMusic.isPlaying) {
      this.backgroundMusic.stop();
    }
    if (this.textTypingTimer) {
      this.textTypingTimer.remove();
    }
    if (this.currentBackground) {
      this.currentBackground.destroy();
    }
    if (this.currentText) {
      this.currentText.destroy();
    }
  }
}
