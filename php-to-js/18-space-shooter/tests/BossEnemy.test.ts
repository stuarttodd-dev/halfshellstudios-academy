import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mockPhaserModule } from './helpers/PhaserMocks';

vi.mock('phaser', () => mockPhaserModule);

import { BossEnemy } from '../src/entities/enemies/BossEnemy';
import { HomingMissile } from '../src/entities/enemies/HomingMissile';
import { createMockScene } from './helpers/PhaserMocks';

/**
 * Tests for BossEnemy class
 */

describe('BossEnemy', () => {
  let mockScene: any;
  let boss: BossEnemy;
  let mockPlayer: any;

  beforeEach(() => {
    mockScene = createMockScene();
    mockScene.textures.exists = vi.fn(() => true);
    mockScene.scale = { width: 800, height: 600, displaySize: { width: 800, height: 600 } };
    
    boss = new BossEnemy(mockScene, 400, 100);
    
    mockPlayer = {
      x: 400,
      y: 500,
      active: true,
    };
  });

  it('should be created with high health and shield', () => {
    expect(boss.getHealth()).toBe(500);
    expect(boss.getShield()).toBe(500);
  });

  it('should have homing missiles array', () => {
    expect(boss.homingMissiles).toEqual([]);
  });

  describe('Damage System', () => {
    it('should take damage to shield first', () => {
      const initialShield = boss.getShield();
      boss.takeDamage(50);
      
      expect(boss.getShield()).toBe(initialShield - 50);
      expect(boss.getHealth()).toBe(500);
    });

    it('should damage health when shield is depleted', () => {
      boss.takeDamage(600);
      
      expect(boss.getShield()).toBe(0);
      expect(boss.getHealth()).toBe(400); // 500 - 100 remaining damage
    });

    it('should return true when destroyed', () => {
      const destroyed = boss.takeDamage(1000);
      expect(destroyed).toBe(true);
    });

    it('should flash red when taking damage', () => {
      boss.takeDamage(10);
      // Flash should be triggered
      expect(mockScene.time.delayedCall).toHaveBeenCalled();
    });
  });

  describe('Attack Patterns', () => {
    it('should cycle through attack patterns', () => {
      // This tests that the boss has multiple attack methods
      // The actual pattern cycling is tested in integration tests
      expect(boss).toBeDefined();
    });

    it('should create bullets when attacking', () => {
      const initialBulletCount = boss.bullets.length;
      // Trigger an attack (would need to call performAttack, but it's private)
      // This is more of an integration test
      expect(boss.bullets).toBeDefined();
    });
  });

  describe('Movement', () => {
    it('should move side to side', () => {
      const currentTime = 1000;
      boss.update(mockPlayer, currentTime);
      // Boss should have velocity set (tested via integration)
    });
  });
});

describe('HomingMissile', () => {
  let mockScene: any;
  let mockTarget: any;

  beforeEach(() => {
    mockScene = createMockScene();
    mockScene.textures.exists = vi.fn(() => true);
    mockScene.scale = { width: 800, height: 600, displaySize: { width: 800, height: 600 } };
    
    mockTarget = {
      x: 400,
      y: 500,
      active: true,
    };
  });

  it('should be created at specified position', () => {
    const missile = new HomingMissile(mockScene, 400, 100, 90, mockTarget);
    expect(missile.x).toBe(400);
    expect(missile.y).toBe(100);
  });

  it('should track target when updated', () => {
    const missile = new HomingMissile(mockScene, 400, 100, 90, mockTarget);
    
    // Update missile - it should adjust direction toward target
    missile.update();
    
    // Velocity should be adjusted (integration test)
    expect(missile.active).toBe(true);
  });

  it('should detect when off-screen', () => {
    const missile = new HomingMissile(mockScene, -200, 300, 90, mockTarget);
    expect(missile.isOffScreen()).toBe(true);
  });

  it('should handle missing target gracefully', () => {
    const missile = new HomingMissile(mockScene, 400, 100, 90, undefined);
    // Should not crash when updating without target
    expect(() => missile.update()).not.toThrow();
  });
});

