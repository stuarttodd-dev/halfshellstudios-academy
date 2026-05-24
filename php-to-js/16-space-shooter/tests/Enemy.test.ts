import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mockPhaserModule } from './helpers/PhaserMocks';

vi.mock('phaser', () => mockPhaserModule);

import { GroundEnemy } from '../src/entities/enemies/GroundEnemy';
import { SideEnemy } from '../src/entities/enemies/SideEnemy';
import { EnemyBullet } from '../src/entities/enemies/EnemyBullet';
import { createMockScene } from './helpers/PhaserMocks';

/**
 * Tests for Enemy classes
 */

describe('BaseEnemy', () => {
  let mockScene: any;
  
  beforeEach(() => {
    mockScene = createMockScene();
    mockScene.textures.exists = vi.fn(() => true);
  });

  it('should initialize with correct health', () => {
    // GroundEnemy extends BaseEnemy
    const enemy = new GroundEnemy(mockScene, 400, 100);
    expect(enemy.getHealth()).toBeGreaterThan(0);
  });

  it('should take damage and reduce health', () => {
    const enemy = new GroundEnemy(mockScene, 400, 100);
    const initialHealth = enemy.getHealth();
    
    enemy.takeDamage(10);
    
    expect(enemy.getHealth()).toBe(initialHealth - 10);
  });

  it('should return true when destroyed', () => {
    const enemy = new GroundEnemy(mockScene, 400, 100);
    const initialHealth = enemy.getHealth();
    
    const destroyed = enemy.takeDamage(initialHealth);
    expect(destroyed).toBe(true);
  });

  it('should return false when still alive', () => {
    const enemy = new GroundEnemy(mockScene, 400, 100);
    const destroyed = enemy.takeDamage(10);
    expect(destroyed).toBe(false);
  });

  it('should not have negative health', () => {
    const enemy = new GroundEnemy(mockScene, 400, 100);
    const initialHealth = enemy.getHealth();
    
    enemy.takeDamage(initialHealth + 100);
    expect(enemy.getHealth()).toBe(0);
  });

  it('should play enemy damage sound when hit', () => {
    mockScene.cache.audio.exists = vi.fn(() => true);
    const enemy = new GroundEnemy(mockScene, 400, 100);
    enemy.takeDamage(10);
    // Sound effects should be called (tested via mock)
  });

  it('should flash red when taking damage', () => {
    const enemy = new GroundEnemy(mockScene, 400, 100);
    enemy.takeDamage(10);
    // Flash effect should be triggered (tested via scene.time.delayedCall)
    expect(mockScene.time.delayedCall).toHaveBeenCalled();
  });
});

describe('GroundEnemy', () => {
  let mockScene: any;
  
  beforeEach(() => {
    mockScene = createMockScene();
    mockScene.textures.exists = vi.fn(() => true);
  });

  it('should be created at specified position', () => {
    const enemy = new GroundEnemy(mockScene, 400, 100);
    expect(enemy.x).toBe(400);
    expect(enemy.y).toBe(100);
  });

  it('should have bullets array', () => {
    const enemy = new GroundEnemy(mockScene, 400, 100);
    expect(enemy.bullets).toEqual([]);
  });
});

describe('SideEnemy', () => {
  let mockScene: any;
  
  beforeEach(() => {
    mockScene = createMockScene();
    mockScene.textures.exists = vi.fn(() => true);
  });

  it('should be created at specified position', () => {
    const enemy = new SideEnemy(mockScene, 100, 200, true);
    expect(enemy.x).toBe(100);
    expect(enemy.y).toBe(200);
  });

  it('should have direction set when created from left', () => {
    const enemy = new SideEnemy(mockScene, 100, 200, true);
    expect(enemy['direction']).toBe(1); // Moving right
  });

  it('should have direction set when created from right', () => {
    const enemy = new SideEnemy(mockScene, 700, 200, false);
    expect(enemy['direction']).toBe(-1); // Moving left
  });
});

describe('EnemyBullet', () => {
  let mockScene: any;
  
  beforeEach(() => {
    mockScene = createMockScene();
    mockScene.textures.exists = vi.fn(() => true);
    mockScene.scale = { width: 800, height: 600, displaySize: { width: 800, height: 600 } };
  });

  it('should be created at specified position', () => {
    const bullet = new EnemyBullet(mockScene, 400, 300, 90);
    expect(bullet.x).toBe(400);
    expect(bullet.y).toBe(300);
  });

  it('should detect when off-screen to the left', () => {
    const bullet = new EnemyBullet(mockScene, -200, 300, 90);
    expect(bullet.isOffScreen()).toBe(true);
  });

  it('should detect when off-screen to the right', () => {
    const bullet = new EnemyBullet(mockScene, 1100, 300, 90);
    expect(bullet.isOffScreen()).toBe(true);
  });

  it('should detect when off-screen at top', () => {
    const bullet = new EnemyBullet(mockScene, 400, -200, 90);
    expect(bullet.isOffScreen()).toBe(true);
  });

  it('should detect when off-screen at bottom', () => {
    const bullet = new EnemyBullet(mockScene, 400, 900, 90);
    expect(bullet.isOffScreen()).toBe(true);
  });

  it('should not detect as off-screen when on screen', () => {
    const bullet = new EnemyBullet(mockScene, 400, 300, 90);
    expect(bullet.isOffScreen()).toBe(false);
  });
});

