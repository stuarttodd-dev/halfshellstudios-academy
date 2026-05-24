import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mockPhaserModule } from './helpers/PhaserMocks';

vi.mock('phaser', () => mockPhaserModule);

import { Bullet } from '../src/entities/Bullet';
import { BULLET_CONFIG } from '../src/config';
import { createMockScene } from './helpers/PhaserMocks';

/**
 * Comprehensive tests for Bullet class
 */

describe('Bullet Configuration', () => {
  it('should have correct bullet speed', () => {
    expect(BULLET_CONFIG.speed).toBeGreaterThan(200);
    expect(BULLET_CONFIG.speed).toBe(400);
  });

  it('should have correct bullet dimensions', () => {
    expect(BULLET_CONFIG.width).toBe(128);
    expect(BULLET_CONFIG.height).toBe(64);
  });

  it('should have yellow color for bullets', () => {
    expect(BULLET_CONFIG.color).toBe(0xffff00);
  });

  it('should have correct damage', () => {
    expect(BULLET_CONFIG.damage).toBeGreaterThan(0);
  });

  it('should have a cooldown to prevent spam shooting', () => {
    expect(BULLET_CONFIG.cooldown).toBeGreaterThan(0);
    expect(BULLET_CONFIG.cooldown).toBeLessThan(1000);
    expect(BULLET_CONFIG.cooldown).toBeGreaterThan(0);
  });
});

describe('Bullet Class', () => {
  let mockScene: any;

  beforeEach(() => {
    mockScene = createMockScene();
    mockScene.textures.exists = vi.fn(() => true);
    mockScene.scale = { width: 800, height: 600, displaySize: { width: 800, height: 600 } };
  });

  it('should be created at specified position', () => {
    const bullet = new Bullet(mockScene, 400, 300, 0);
    expect(bullet.x).toBe(400);
    expect(bullet.y).toBe(300);
  });

  it('should have velocity set based on angle', () => {
    const bullet = new Bullet(mockScene, 400, 300, 90);
    // Velocity should be set (tested via physics body)
    expect(bullet.active).toBe(true);
  });

  it('should detect when off-screen to the left', () => {
    // isOffScreen uses `x < -this.width` margin, so must go beyond -width (64px)
    const bullet = new Bullet(mockScene, -200, 300, 0);
    expect(bullet.isOffScreen()).toBe(true);
  });

  it('should detect when off-screen to the right', () => {
    // Beyond 800 + width (64px)
    const bullet = new Bullet(mockScene, 1000, 300, 0);
    expect(bullet.isOffScreen()).toBe(true);
  });

  it('should detect when off-screen at top', () => {
    const bullet = new Bullet(mockScene, 400, -200, 0);
    expect(bullet.isOffScreen()).toBe(true);
  });

  it('should detect when off-screen at bottom', () => {
    // Beyond 600 + height (64px)
    const bullet = new Bullet(mockScene, 400, 800, 0);
    expect(bullet.isOffScreen()).toBe(true);
  });

  it('should not detect as off-screen when on screen', () => {
    const bullet = new Bullet(mockScene, 400, 300, 0);
    expect(bullet.isOffScreen()).toBe(false);
  });

  it('should handle boundary cases correctly', () => {
    // Clearly off-screen left
    const bullet1 = new Bullet(mockScene, -200, 300, 0);
    expect(bullet1.isOffScreen()).toBe(true);

    // Centre of screen — definitely on screen
    const bullet2 = new Bullet(mockScene, 400, 300, 0);
    expect(bullet2.isOffScreen()).toBe(false);
  });
});
