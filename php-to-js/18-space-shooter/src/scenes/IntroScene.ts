import Phaser from 'phaser';
import { SCENE_KEYS } from '../config';
import { isMobileDevice } from '../utils/TouchControls';

/**
 * IntroScene - Star Wars style opening crawl
 * Shows epic story text before the game begins
 */
export class IntroScene extends Phaser.Scene {
  constructor() {
    super({ key: SCENE_KEYS.INTRO });
  }

  create() {
    const { width, height } = this.scale;

    // Black background (space)
    this.add.rectangle(width / 2, height / 2, width, height, 0x000000);
    
    // The epic story text
    const episodeTitle = this.add.text(width / 2, height / 2 - 100, 'EPISODE I', {
      fontSize: '48px',
      color: '#ffcc00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
      letterSpacing: 10,
    });
    episodeTitle.setOrigin(0.5);
    episodeTitle.setDepth(2);

    const subtitle = this.add.text(width / 2, height / 2, 'THE LAST HOPE', {
      fontSize: '36px',
      color: '#ffcc00',
      fontFamily: 'Arial',
      fontStyle: 'bold',
      letterSpacing: 5,
    });
    subtitle.setOrigin(0.5);
    subtitle.setDepth(2);

    // Fade out the episode title after a few seconds (quicker start)
    this.tweens.add({
      targets: [episodeTitle, subtitle],
      alpha: 0,
      duration: 1500,
      delay: 2000, // Reduced from 3000ms to start quicker
      onComplete: () => {
        episodeTitle.setVisible(false);
        subtitle.setVisible(false);
      },
    });

    const storyText = `A LONG TIME AGO,
IN A GALAXY FAR, FAR AWAY...


It has been 500 years since
the Great Invasion began.

Earth fell to an alien armada
unlike anything humanity had
ever encountered.

We were unprepared.
We were overwhelmed.

The entire planet was enslaved.

Humanity became a resource,
harvested and subjugated
by our alien overlords.

But there was hope...

You escaped.

With a stolen prototype weapon,
you broke free from your prison
and activated the time portal.

A one-way mission through time.

You have returned to the
moment of the invasion,
armed with future knowledge
and technology.

This is your mission:
Eliminate as many alien
threats as possible.

Save as many lives as you can.

For every human that evacuates,
humanity gains a soldier.

We need 100,000 evacuees
to mount a resistance
and save the enslaved
human race.

There is no way back.
There is no escape.
There is only victory...

...or death.

May the souls of those you
save guide your aim.

The fate of humanity
rests in your hands.



Good luck, soldier.....`;

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

    // Animate the container scrolling up (Star Wars crawl effect) - faster movement
    const crawlDuration = 35000; // Reduced from 50000ms (30% faster)
    this.tweens.add({
      targets: textContainer,
      y: -totalHeight,
      duration: crawlDuration,
      ease: 'Linear',
    });

    // Transition to cutscene 1 after crawl completes
    this.time.delayedCall(crawlDuration, () => {
      this.scene.start(SCENE_KEYS.CUTSCENE_1);
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
      this.scene.start(SCENE_KEYS.CUTSCENE_1);
    };

    this.input.keyboard?.once('keydown-SPACE', skipIntro);
    this.input.once('pointerdown', skipIntro);
  }
}

