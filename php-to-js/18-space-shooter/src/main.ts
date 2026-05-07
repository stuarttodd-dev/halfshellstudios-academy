import Phaser from 'phaser';
import { GAME_CONFIG } from './config';
import { LoadingScene } from './scenes/LoadingScene';
import { StartScene } from './scenes/StartScene';
import { IntroScene } from './scenes/IntroScene';
import { Cutscene1Scene } from './scenes/cutscenes/Cutscene1Scene';
import { GameScene } from './scenes/GameScene';
import { GameOverScene } from './scenes/GameOverScene';
import { Episode2Scene } from './scenes/Episode2Scene';
import { initializeScores } from './utils/ScoreManager';

/**
 * Main entry point for the game
 * This initializes Phaser with our configuration and registers all scenes
 */

// Initialize scores from file on startup (before creating Phaser game)
initializeScores().then(() => {
  console.log('✅ Scores initialized from file');
  startGame();
}).catch((error) => {
  console.error('❌ Failed to initialize scores:', error);
  startGame(); // Start game anyway
});

function startGame() {
  // Dashboard height (space at bottom for controls - always visible)
  const DASHBOARD_HEIGHT = 200;
  // Increase base game height for more playable area
  const BASE_GAME_HEIGHT = GAME_CONFIG.height + 100; // Added 100px more height
  
  const config: Phaser.Types.Core.GameConfig = {
  type: Phaser.WEBGL, // Force WEBGL for RGBA support
  width: GAME_CONFIG.width,
  height: BASE_GAME_HEIGHT + DASHBOARD_HEIGHT,
  parent: 'game-container',
  backgroundColor: GAME_CONFIG.backgroundColor,
  physics: GAME_CONFIG.physics,
  scene: [LoadingScene, StartScene, IntroScene, Cutscene1Scene, GameScene, GameOverScene, Episode2Scene], // Register all scenes (starts with LoadingScene)
  dom: {
    createContainer: true, // Enable DOM container for HTML elements (needed for input fields)
  },
    scale: {
      // Use RESIZE mode to fill 100% of viewport height
      mode: Phaser.Scale.RESIZE,
      autoCenter: Phaser.Scale.CENTER_BOTH, // Center the game canvas horizontally and vertically
      width: GAME_CONFIG.width, // Base game width (internal resolution)
      height: BASE_GAME_HEIGHT + DASHBOARD_HEIGHT, // Base game height + dashboard space
      min: {
        width: 400, // Minimum width (for very small screens)
        height: 300 + DASHBOARD_HEIGHT, // Minimum height + dashboard space
      },
      max: {
        width: 800, // Maximum width - enforce 800px max
        height: window.innerHeight, // Maximum height (full screen height)
      },
      expandParent: true, // Allow canvas to expand to fill parent container
      // Add CSS styling to ensure proper centering
      autoRound: true,
    },
  render: {
    // Ensure WebGL handles transparency correctly
    antialias: true,
    pixelArt: false, // Set to false to preserve smooth edges on transparent images
    roundPixels: false,
  },
};

  // Create and start the Phaser game
  new Phaser.Game(config);
}

