import Phaser from 'phaser';
import { SCENE_KEYS } from '../config';
import { isMobileDevice } from '../utils/TouchControls';

/**
 * Episode2Scene - Episode 2 opening crawl after reaching 100,000 humans evacuated
 */
export class Episode2Scene extends Phaser.Scene {
  constructor() {
    super({ key: SCENE_KEYS.EPISODE_2 });
  }

  create() {
    // Stop game scene background music
    try {
      const gameSceneMusic = this.sound.get('gamescene');
      if (gameSceneMusic && gameSceneMusic.isPlaying) {
        gameSceneMusic.stop();
        console.log('🛑 Game scene music stopped');
      }
    } catch (e) {
      // Ignore errors
    }

    const { width, height } = this.scale;

    // Black background (space)
    this.add.rectangle(width / 2, height / 2, width, height, 0x000000);
    
    // Episode 2 title
    const episodeTitle = this.add.text(width / 2, height / 2 - 100, 'EPISODE II', {
      fontSize: '48px',
      color: '#ffcc00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
      letterSpacing: 10,
    });
    episodeTitle.setOrigin(0.5);
    episodeTitle.setDepth(2);

    const subtitle = this.add.text(width / 2, height / 2, 'TBC', {
      fontSize: '36px',
      color: '#ffcc00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
      letterSpacing: 5,
    });
    subtitle.setOrigin(0.5);
    subtitle.setDepth(2);

    // Fade out the episode title after a few seconds
    this.tweens.add({
      targets: [episodeTitle, subtitle],
      alpha: 0,
      duration: 2000,
      delay: 3000,
      onComplete: () => {
        episodeTitle.setVisible(false);
        subtitle.setVisible(false);
      },
    });

    const storyText = `A LONG TIME AGO,
IN A GALAXY FAR, FAR AWAY...


TO BE CONTINUED...`;

    // Create text with perspective effect (Star Wars style)
    const textStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: '24px',
      color: '#ffcc00', // Yellow/gold text like Star Wars
      fontFamily: 'Arial',
      align: 'center',
      wordWrap: { width: width - 100 },
      lineSpacing: 8,
    };

    // Create a container to hold the text for perspective effect
    const textContainer = this.add.container(width / 2, height + 200);
    
    // Create the text starting below the visible area
    const text = this.add.text(0, 0, storyText, textStyle);
    text.setOrigin(0.5, 0);
    text.setDepth(1);
    
    // Add text to container
    textContainer.add(text);
    
    // Apply perspective effect (3D tilt like Star Wars)
    textContainer.setScale(1.2, 1);

    // Calculate total height
    const totalHeight = text.height + 200;

    // Animate the container scrolling up (Star Wars crawl effect)
    this.tweens.add({
      targets: textContainer,
      y: -totalHeight,
      duration: 15000, // 15 seconds for the crawl
      ease: 'Linear',
    });

    // Transition back to start scene after crawl completes
    this.time.delayedCall(15000, () => {
      this.scene.start(SCENE_KEYS.START);
    });

    // Allow skipping with spacebar or click
    const skipTextValue = isMobileDevice() ? 'Tap to Skip' : 'Press SPACE to Skip';
    const skipText = this.add.text(
      width / 2,
      height - 30,
      skipTextValue,
      {
        fontSize: '18px',
        color: '#ffffff',
        fontFamily: 'Arial',
        // alpha set via setAlpha after creation
      }
    );
    skipText.setOrigin(0.5);
    skipText.setAlpha(0.7);
    skipText.setDepth(2);

    // Skip functionality
    const skipIntro = () => {
      this.scene.start(SCENE_KEYS.START);
    };

    this.input.keyboard?.once('keydown-SPACE', skipIntro);
    this.input.once('pointerdown', skipIntro);
  }
}

