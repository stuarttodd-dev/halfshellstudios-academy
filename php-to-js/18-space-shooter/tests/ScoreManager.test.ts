import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import {
  saveScore,
  getScores,
  clearScores,
  isHighScore,
} from '../src/utils/ScoreManager';

// ScoreManager uses localStorage — jsdom provides a working implementation.

describe('ScoreManager', () => {
  beforeEach(() => {
    clearScores();
    localStorage.clear();
  });

  afterEach(() => {
    clearScores();
    localStorage.clear();
  });

  describe('saveScore', () => {
    it('saves a score and makes it retrievable', () => {
      saveScore('AAA', 5000);
      const scores = getScores();
      expect(scores.some(s => s.name === 'AAA' && s.score === 5000)).toBe(true);
    });

    it('uppercases the player name', () => {
      saveScore('aaa', 1000);
      const scores = getScores();
      expect(scores[0].name).toBe('AAA');
    });

    it('trims whitespace from the name', () => {
      saveScore('  BB ', 2000);
      const scores = getScores();
      expect(scores[0].name).toBe('BB');
    });

    it('truncates names longer than 5 characters', () => {
      saveScore('TOOLONGNAME', 3000);
      const scores = getScores();
      expect(scores[0].name).toBe('TOOLO');
      expect(scores[0].name.length).toBeLessThanOrEqual(5);
    });

    it('uses PLAYER as fallback for empty names', () => {
      saveScore('', 4000);
      const scores = getScores();
      expect(scores[0].name).toBe('PLAYER');
    });

    it('uses PLAYER as fallback for whitespace-only names', () => {
      saveScore('   ', 4000);
      const scores = getScores();
      expect(scores[0].name).toBe('PLAYER');
    });

    it('includes an ISO date string', () => {
      saveScore('CCC', 1000);
      const scores = getScores();
      expect(() => new Date(scores[0].date)).not.toThrow();
      expect(new Date(scores[0].date).toISOString()).toBe(scores[0].date);
    });

    it('persists to localStorage', () => {
      saveScore('DDD', 9999);
      const raw = localStorage.getItem('spaceShooterHighScores');
      expect(raw).not.toBeNull();
      const parsed = JSON.parse(raw!);
      expect(parsed.some((s: any) => s.name === 'DDD')).toBe(true);
    });
  });

  describe('Score ordering', () => {
    it('stores scores in descending order (highest first)', () => {
      saveScore('LOW', 100);
      saveScore('MID', 500);
      saveScore('TOP', 1000);
      const scores = getScores();
      expect(scores[0].score).toBeGreaterThanOrEqual(scores[1].score);
      expect(scores[1].score).toBeGreaterThanOrEqual(scores[2].score);
    });

    it('keeps only the top 5 scores', () => {
      for (let i = 1; i <= 10; i++) {
        saveScore('TST', i * 100);
      }
      const scores = getScores();
      expect(scores.length).toBeLessThanOrEqual(5);
    });

    it('retains the highest scores when capped', () => {
      for (let i = 1; i <= 10; i++) {
        saveScore('TST', i * 100);
      }
      const scores = getScores();
      // All retained scores should be in the top half
      scores.forEach(s => expect(s.score).toBeGreaterThanOrEqual(600));
    });
  });

  describe('clearScores', () => {
    it('removes all stored scores', () => {
      saveScore('EEE', 5000);
      clearScores();
      // After clear, localStorage key should be gone
      expect(localStorage.getItem('spaceShooterHighScores')).toBeNull();
    });
  });

  describe('isHighScore', () => {
    it('returns true when there are fewer than 5 scores', () => {
      saveScore('AAA', 1000);
      expect(isHighScore(1)).toBe(true);
    });

    it('returns true for a score higher than the lowest top-5 score', () => {
      for (let i = 1; i <= 5; i++) {
        saveScore('TST', i * 1000);
      }
      // Lowest of the top 5 is 1000; 1001 should qualify
      expect(isHighScore(1001)).toBe(true);
    });

    it('returns false for a score not better than the lowest top-5 score', () => {
      for (let i = 1; i <= 5; i++) {
        saveScore('TST', i * 1000);
      }
      // Lowest is 1000; 1000 is not strictly greater
      expect(isHighScore(1000)).toBe(false);
    });

    it('returns false for a score well below the bottom of the table', () => {
      for (let i = 1; i <= 5; i++) {
        saveScore('TST', i * 10000);
      }
      expect(isHighScore(1)).toBe(false);
    });
  });
});
