/**
 * Game configuration constants
 * This file centralizes all game settings for easy adjustment
 */
export const GAME_CONFIG = {
  width: 800,
  height: 600,
  backgroundColor: '#000011', // Dark blue space color
  physics: {
    default: 'arcade',
    arcade: {
      gravity: { x: 0, y: 0 }, // No gravity in space
      debug: false, // Set to true to see physics bodies
    },
  },
} as const;

/**
 * Player configuration
 */
export const PLAYER_CONFIG = {
  speed: 250, // Increased for more responsive feel
  acceleration: 500, // Acceleration rate (pixels per second squared)
  deceleration: 400, // Deceleration rate (friction in space is less)
  rotationSpeed: 120, // Rotation speed (degrees per second) - reduced for smoother control
  startX: 400,
  startY: 500,
  width: 64, // Doubled from 32
  height: 64, // Doubled from 32
  color: 0x00ff00, // Green spaceship
  maxHull: 100,
  maxShield: 100,
  shieldRadius: 40, // Radius of the shield circle around the ship
  shieldColor: 0x00ffff, // Cyan/blue shield color
  shieldAlpha: 0.6, // Transparency (0 = invisible, 1 = opaque)
} as const;

/**
 * Bullet configuration
 */
export const BULLET_CONFIG = {
  speed: 400, // Faster than player
  width: 128, // Match PNG size exactly (128x64)
  height: 64, // Match PNG size exactly
  color: 0xffff00, // Yellow bullets
  cooldown: 300, // Milliseconds between shots
  damage: 23, // Damage dealt to enemies (15 + 50% = 22.5, rounded to 23)
} as const;

/**
 * Enemy bullet configuration
 */
export const ENEMY_BULLET_CONFIG = {
  damage: 5, // Damage dealt to player
  width: 64, // Match PNG size exactly (64x128)
  height: 128, // Match PNG size exactly
} as const;

/**
 * Enemy spawn configuration
 */
export const ENEMY_CONFIG = {
  groundSpawnY: 550, // Y position for ground enemies (near bottom, they shoot up)
  sideSpawnY: 200, // Y position for side enemies (middle of screen)
  spawnInterval: 1500, // Milliseconds between enemy spawns (twice as fast)
} as const;

/**
 * Score configuration (multiplied by 100 - represents humans evacuated)
 */
export const SCORE_CONFIG = {
  groundEnemy: 500, // Humans evacuated for defeating ground enemy (halved from 1000)
  sideEnemy: 750, // Humans evacuated for defeating side enemy (halved from 1500)
  boss: 25000, // Humans evacuated for defeating boss (halved from 50000)
  bulletVsBullet: 50, // Humans evacuated for bullet collision (halved from 100)
} as const;

/**
 * Scene keys - used to identify different scenes
 */
export const SCENE_KEYS = {
  LOADING: 'LoadingScene',
  START: 'StartScene',
  INTRO: 'IntroScene',
  CUTSCENE_1: 'Cutscene1Scene',
  GAME: 'GameScene',
  GAME_OVER: 'GameOverScene',
  EPISODE_2: 'Episode2Scene',
} as const;

