import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mockPhaserModule } from './helpers/PhaserMocks';

vi.mock('phaser', () => mockPhaserModule);

import { SpawnController } from '../src/SpawnController';
import { createMockScene } from './helpers/PhaserMocks';
import { ENEMY_CONFIG } from '../src/config';

describe('SpawnController', () => {
  let mockScene: any;
  let enemies: any[];
  let controller: SpawnController;

  beforeEach(() => {
    mockScene = createMockScene();
    enemies = [];
    controller = new SpawnController(mockScene, enemies);
  });

  describe('Initial state', () => {
    it('starts with zero enemies defeated', () => {
      expect(controller.enemiesDefeated).toBe(0);
    });

    it('starts with boss not spawned', () => {
      expect(controller.bossSpawned).toBe(false);
    });

    it('starts with no boss reference', () => {
      expect(controller.boss).toBeUndefined();
    });
  });

  describe('enemiesDefeated counter', () => {
    it('can be incremented externally', () => {
      controller.enemiesDefeated += 1;
      expect(controller.enemiesDefeated).toBe(1);
    });

    it('accumulates across multiple kills', () => {
      controller.enemiesDefeated += 10;
      controller.enemiesDefeated += 5;
      expect(controller.enemiesDefeated).toBe(15);
    });
  });

  describe('Boss spawn threshold', () => {
    it('does not spawn boss below 25 enemies defeated', () => {
      controller.enemiesDefeated = 24;
      controller.update(99999); // large time to force spawn attempt
      expect(controller.bossSpawned).toBe(false);
    });

    it('spawns boss when exactly 25 enemies are defeated', () => {
      controller.enemiesDefeated = 25;
      controller.update(99999);
      expect(controller.bossSpawned).toBe(true);
    });

    it('spawns boss when more than 25 enemies are defeated', () => {
      controller.enemiesDefeated = 30;
      controller.update(99999);
      expect(controller.bossSpawned).toBe(true);
    });

    it('only spawns boss once even if threshold exceeded multiple times', () => {
      controller.enemiesDefeated = 25;
      controller.update(99999);
      controller.update(99999);
      controller.update(99999);
      // bossSpawned should still be true, not reset
      expect(controller.bossSpawned).toBe(true);
    });

    it('adds boss to the enemies array', () => {
      controller.enemiesDefeated = 25;
      controller.update(99999);
      // boss should be added to the enemies list
      expect(enemies.length).toBeGreaterThan(0);
      expect(controller.boss).toBeDefined();
    });

    it('sets the boss reference', () => {
      controller.enemiesDefeated = 25;
      controller.update(99999);
      expect(controller.boss).toBeDefined();
    });
  });

  describe('Spawn interval', () => {
    it('does not spawn enemies before the interval elapses', () => {
      controller.update(0);
      const beforeCount = enemies.length;
      controller.update(ENEMY_CONFIG.spawnInterval - 1);
      expect(enemies.length).toBe(beforeCount);
    });

    it('spawns enemies after the interval elapses', () => {
      controller.update(0);
      const initialCount = enemies.length;
      controller.update(ENEMY_CONFIG.spawnInterval + 1);
      expect(enemies.length).toBeGreaterThan(initialCount);
    });

    it('resets the spawn timer after spawning', () => {
      controller.update(ENEMY_CONFIG.spawnInterval + 1);
      const afterFirstSpawn = enemies.length;
      // Just 1ms later — should NOT spawn again
      controller.update(ENEMY_CONFIG.spawnInterval + 2);
      expect(enemies.length).toBe(afterFirstSpawn);
    });
  });
});
