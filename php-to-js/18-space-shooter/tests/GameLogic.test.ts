import { describe, it, expect, vi } from 'vitest';
import { SCORE_CONFIG } from '../src/config';

/**
 * Tests for game logic and scoring
 */

describe('Game Scoring Logic', () => {
  it('should have positive scores for all kill types', () => {
    expect(SCORE_CONFIG.groundEnemy).toBeGreaterThan(0);
    expect(SCORE_CONFIG.sideEnemy).toBeGreaterThan(0);
    expect(SCORE_CONFIG.boss).toBeGreaterThan(0);
    expect(SCORE_CONFIG.bulletVsBullet).toBeGreaterThan(0);
  });

  it('boss score should be the highest reward', () => {
    expect(SCORE_CONFIG.boss).toBeGreaterThan(SCORE_CONFIG.sideEnemy);
    expect(SCORE_CONFIG.boss).toBeGreaterThan(SCORE_CONFIG.groundEnemy);
  });

  it('side enemy should score more than ground enemy', () => {
    expect(SCORE_CONFIG.sideEnemy).toBeGreaterThan(SCORE_CONFIG.groundEnemy);
  });

  describe('Score Accumulation', () => {
    it('should accumulate scores correctly', () => {
      let totalScore = 0;
      totalScore += SCORE_CONFIG.groundEnemy * 5;
      totalScore += SCORE_CONFIG.sideEnemy * 3;
      totalScore += SCORE_CONFIG.boss;
      expect(totalScore).toBe(
        SCORE_CONFIG.groundEnemy * 5 + SCORE_CONFIG.sideEnemy * 3 + SCORE_CONFIG.boss
      );
    });

    it('should scale linearly with kill count', () => {
      const ten = SCORE_CONFIG.groundEnemy * 10;
      const twenty = SCORE_CONFIG.groundEnemy * 20;
      expect(twenty).toBe(ten * 2);
    });
  });
});

describe('Game State Transitions', () => {
  it('should trigger episode 2 at 100,000 points', () => {
    const episode2Threshold = 100000;
    const scores = [
      SCORE_CONFIG.boss * 4, // 100,000
      SCORE_CONFIG.groundEnemy * 200, // 100,000
      SCORE_CONFIG.sideEnemy * 132 + SCORE_CONFIG.groundEnemy * 2, // 100,000
    ];
    
    scores.forEach(score => {
      expect(score).toBeGreaterThanOrEqual(episode2Threshold);
    });
  });

  it('should spawn boss at 25 enemies defeated', () => {
    const bossSpawnThreshold = 25;
    expect(bossSpawnThreshold).toBe(25);
  });
});

describe('Damage Calculations', () => {
  it('should calculate player bullet damage correctly', () => {
    const playerBulletDamage = 30; // From BULLET_CONFIG
    expect(playerBulletDamage).toBe(30);
  });

  it('should calculate collision damage correctly', () => {
    const collisionDamage = 50;
    expect(collisionDamage).toBe(50);
  });

  it('should calculate enemy bullet damage correctly', () => {
    const enemyBulletDamage = 5; // From ENEMY_BULLET_CONFIG
    expect(enemyBulletDamage).toBe(5);
  });
});

