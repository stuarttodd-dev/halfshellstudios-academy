import Phaser from 'phaser';
import { SCENE_KEYS } from '../config';
import { scaleSize } from '../utils/ResponsiveScale';

/**
 * LoadingScene - Shows loading progress while assets are being loaded
 * This scene runs first and loads all game assets, then transitions to StartScene
 */
export class LoadingScene extends Phaser.Scene {
  private progressBar?: Phaser.GameObjects.Graphics;
  private progressBarBg?: Phaser.GameObjects.Graphics;
  private progressText?: Phaser.GameObjects.Text;
  private loadingText?: Phaser.GameObjects.Text;

  constructor() {
    super({ key: 'LoadingScene' });
  }

  /**
   * Preload - Load all game assets
   */
  preload() {
    const { width, height } = this.scale;

    // Create loading text
    this.loadingText = this.add.text(
      width / 2,
      height / 2 - 60,
      'Loading...',
      {
        fontSize: `${scaleSize(this, 32)}px`,
        color: '#ffffff',
        fontFamily: 'Arial',
        fontStyle: 'bold',
      }
    );
    this.loadingText.setOrigin(0.5);

    // Create progress bar background
    const barWidth = scaleSize(this, 400);
    const barHeight = scaleSize(this, 30);
    const barX = width / 2 - barWidth / 2;
    const barY = height / 2;

    this.progressBarBg = this.add.graphics();
    this.progressBarBg.fillStyle(0x333333, 1);
    this.progressBarBg.fillRect(barX, barY, barWidth, barHeight);

    // Create progress bar
    this.progressBar = this.add.graphics();

    // Progress text
    this.progressText = this.add.text(
      width / 2,
      height / 2 + 80,
      '0%',
      {
        fontSize: `${scaleSize(this, 24)}px`,
        color: '#ffffff',
        fontFamily: 'Arial',
      }
    );
    this.progressText.setOrigin(0.5);

    // Listen for file progress
    this.load.on('progress', (progress: number) => {
      // Update progress bar
      this.progressBar?.clear();
      this.progressBar?.fillStyle(0x00ff00, 1); // Green progress bar
      this.progressBar?.fillRect(barX, barY, barWidth * progress, barHeight);

      // Update progress text (show lower percentage - divide by 2)
      const percentage = Math.round(progress * 50); // Max shows 50% instead of 100%
      this.progressText?.setText(`${percentage}%`);
    });

    // Listen for file loading
    this.load.on('fileprogress', (_file: Phaser.Loader.File) => {
      // Optional: show which file is loading
      // console.log(`Loading: ${file.key}`);
    });

    // When loading completes
    this.load.on('complete', () => {
      // Small delay before transitioning to start scene
      this.time.delayedCall(300, () => {
        this.scene.start(SCENE_KEYS.START);
      });
    });

    // Load all assets here (moved from StartScene)
    this.loadAssets();
  }

  /**
   * Load all game assets
   */
  private loadAssets(): void {
    // Load the start scene background image
    this.load.image('startBackground', 'assets/images/start_scene.png');
    
    // Load game images
    this.load.image('gameBackground', 'assets/images/game_scene.png');
    this.load.image('player', 'assets/images/player_ship.png');
    this.load.image('bullet', 'assets/images/player_bullet.png');
    this.load.image('enemyBullet', 'assets/images/enemy_bullet.png');
    this.load.image('groundEnemy', 'assets/images/enemy_ground.png');
    this.load.image('sideEnemy', 'assets/images/enemy_flying.png');
    this.load.image('bossEnemy', 'assets/images/enemy_boss.png');
    
    // Load Half Shell Studios logo from local assets
    this.load.image('halfShellLogo', 'assets/images/primary-logo.png');
    
    // Load game over background
    this.load.image('gameOverBackground', 'assets/images/gameover_background.png');
    
    // Load control panel background
    this.load.image('panel', 'assets/images/panel.png');
    
    // Load fire button image (note: file is fire-button.png with hyphen)
    this.load.image('fireButton', 'assets/images/fire-button.png');
    
    // Load cutscene images
    this.load.image('scene1a', 'assets/images/scene1a.png');
    this.load.image('scene1b', 'assets/images/scene1b.png');
    this.load.image('scene1c', 'assets/images/scene1c.png');
    this.load.image('scene1d', 'assets/images/scene1d.png');
    this.load.image('scene1e', 'assets/images/scene1e.png');
    
    // Load sound effects and music
    this.load.audio('background', 'assets/sounds/background.mp3');
    this.load.audio('gamescene', 'assets/sounds/gamescene.mp3');
    this.load.audio('danger', 'assets/sounds/danger.mp3');
    this.load.audio('explosion', 'assets/sounds/explosion.mp3');
    this.load.audio('shield_damage', 'assets/sounds/shield_damage.mp3');
    this.load.audio('hull_damage', 'assets/sounds/hull_damage.mp3');
    this.load.audio('shield_down', 'assets/sounds/shield_down.mp3');
    this.load.audio('bullet', 'assets/sounds/bullet.mp3');
    this.load.audio('ricochet', 'assets/sounds/ricochet.mp3');
    this.load.audio('cutscene1', 'assets/sounds/cutscene1.mp3');

    // Set up error handler
    this.load.on('fileerror', (file: Phaser.Loader.File) => {
      console.log(`❌ Could not load: ${file.key} from ${file.url}`);
    });
  }

  create() {
    // Scene is created, but assets are still loading
    // Progress updates happen via the load.on('progress') listener
  }
}

