import { describe, it, expect } from 'vitest';
import {
  GAME_CONFIG,
  PLAYER_CONFIG,
  BULLET_CONFIG,
  ENEMY_BULLET_CONFIG,
  ENEMY_CONFIG,
  SCORE_CONFIG,
  SCENE_KEYS,
} from '../src/config';

/**
 * Comprehensive tests for all configuration values
 */

describe('Game Configuration', () => {
  it('should have correct game dimensions', () => {
    expect(GAME_CONFIG.width).toBe(800);
    expect(GAME_CONFIG.height).toBe(600);
  });

  it('should have dark background color', () => {
    expect(GAME_CONFIG.backgroundColor).toBe('#000011');
  });

  it('should have arcade physics enabled', () => {
    expect(GAME_CONFIG.physics.default).toBe('arcade');
    expect(GAME_CONFIG.physics.arcade.gravity.y).toBe(0);
  });
});

describe('Player Configuration', () => {
  it('should have all required player properties', () => {
    expect(PLAYER_CONFIG.speed).toBeDefined();
    expect(PLAYER_CONFIG.startX).toBeDefined();
    expect(PLAYER_CONFIG.startY).toBeDefined();
    expect(PLAYER_CONFIG.width).toBeDefined();
    expect(PLAYER_CONFIG.height).toBeDefined();
    expect(PLAYER_CONFIG.maxHull).toBeDefined();
    expect(PLAYER_CONFIG.maxShield).toBeDefined();
  });

  it('should have reasonable speed values', () => {
    expect(PLAYER_CONFIG.speed).toBeGreaterThan(0);
    expect(PLAYER_CONFIG.acceleration).toBeGreaterThan(0);
    expect(PLAYER_CONFIG.deceleration).toBeGreaterThan(0);
  });

  it('should have positive health values', () => {
    expect(PLAYER_CONFIG.maxHull).toBeGreaterThan(0);
    expect(PLAYER_CONFIG.maxShield).toBeGreaterThan(0);
  });

  it('should have valid shield properties', () => {
    expect(PLAYER_CONFIG.shieldRadius).toBeGreaterThan(0);
    expect(PLAYER_CONFIG.shieldAlpha).toBeGreaterThanOrEqual(0);
    expect(PLAYER_CONFIG.shieldAlpha).toBeLessThanOrEqual(1);
  });
});

describe('Bullet Configuration', () => {
  it('should have all required bullet properties', () => {
    expect(BULLET_CONFIG.speed).toBeDefined();
    expect(BULLET_CONFIG.width).toBeDefined();
    expect(BULLET_CONFIG.height).toBeDefined();
    expect(BULLET_CONFIG.damage).toBeDefined();
    expect(BULLET_CONFIG.cooldown).toBeDefined();
  });

  it('should have bullets faster than player', () => {
    expect(BULLET_CONFIG.speed).toBeGreaterThan(PLAYER_CONFIG.speed);
  });

  it('should have positive damage', () => {
    expect(BULLET_CONFIG.damage).toBeGreaterThan(0);
  });

  it('should have reasonable cooldown', () => {
    expect(BULLET_CONFIG.cooldown).toBeGreaterThan(0);
    expect(BULLET_CONFIG.cooldown).toBeLessThan(1000);
  });
});

describe('Enemy Bullet Configuration', () => {
  it('should have damage value', () => {
    expect(ENEMY_BULLET_CONFIG.damage).toBeDefined();
    expect(ENEMY_BULLET_CONFIG.damage).toBeGreaterThan(0);
  });

  it('should have dimensions', () => {
    expect(ENEMY_BULLET_CONFIG.width).toBeDefined();
    expect(ENEMY_BULLET_CONFIG.height).toBeDefined();
  });
});

describe('Enemy Configuration', () => {
  it('should have spawn positions', () => {
    expect(ENEMY_CONFIG.groundSpawnY).toBeDefined();
    expect(ENEMY_CONFIG.sideSpawnY).toBeDefined();
    expect(ENEMY_CONFIG.spawnInterval).toBeDefined();
  });

  it('should have reasonable spawn interval', () => {
    expect(ENEMY_CONFIG.spawnInterval).toBeGreaterThan(0);
    expect(ENEMY_CONFIG.spawnInterval).toBeLessThanOrEqual(5000);
  });
});

describe('Score Configuration', () => {
  it('should have scores for all enemy types', () => {
    expect(SCORE_CONFIG.groundEnemy).toBeDefined();
    expect(SCORE_CONFIG.sideEnemy).toBeDefined();
    expect(SCORE_CONFIG.boss).toBeDefined();
    expect(SCORE_CONFIG.bulletVsBullet).toBeDefined();
  });

  it('should have positive score values', () => {
    expect(SCORE_CONFIG.groundEnemy).toBeGreaterThan(0);
    expect(SCORE_CONFIG.sideEnemy).toBeGreaterThan(0);
    expect(SCORE_CONFIG.boss).toBeGreaterThan(0);
    expect(SCORE_CONFIG.bulletVsBullet).toBeGreaterThan(0);
  });

  it('should have consistent score ordering (boss > side > ground > bullet)', () => {
    expect(SCORE_CONFIG.boss).toBeGreaterThan(SCORE_CONFIG.sideEnemy);
    expect(SCORE_CONFIG.sideEnemy).toBeGreaterThan(SCORE_CONFIG.groundEnemy);
    expect(SCORE_CONFIG.groundEnemy).toBeGreaterThan(SCORE_CONFIG.bulletVsBullet);
  });

  it('should have boss worth significantly more', () => {
    expect(SCORE_CONFIG.boss).toBeGreaterThan(SCORE_CONFIG.sideEnemy);
    expect(SCORE_CONFIG.boss).toBeGreaterThan(SCORE_CONFIG.groundEnemy);
  });
});

describe('Scene Keys', () => {
  it('should have all required scene keys', () => {
    expect(SCENE_KEYS.START).toBe('StartScene');
    expect(SCENE_KEYS.INTRO).toBe('IntroScene');
    expect(SCENE_KEYS.GAME).toBe('GameScene');
    expect(SCENE_KEYS.GAME_OVER).toBe('GameOverScene');
    expect(SCENE_KEYS.EPISODE_2).toBe('Episode2Scene');
  });
});
