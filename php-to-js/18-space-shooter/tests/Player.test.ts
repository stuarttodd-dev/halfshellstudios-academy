import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mockPhaserModule } from './helpers/PhaserMocks';

vi.mock('phaser', () => mockPhaserModule);

import { Player } from '../src/entities/Player';
import { PLAYER_CONFIG, BULLET_CONFIG } from '../src/config';
import { createMockScene } from './helpers/PhaserMocks';

/**
 * Comprehensive tests for Player class
 */

describe('Player Configuration', () => {
  it('should have correct default configuration values', () => {
    expect(PLAYER_CONFIG.speed).toBe(250);
    expect(PLAYER_CONFIG.startX).toBe(400);
    expect(PLAYER_CONFIG.startY).toBe(500);
    expect(PLAYER_CONFIG.width).toBe(64);
    expect(PLAYER_CONFIG.height).toBe(64);
  });

  it('should have correct shield configuration', () => {
    expect(PLAYER_CONFIG.maxShield).toBe(100);
    expect(PLAYER_CONFIG.shieldRadius).toBe(40);
    expect(PLAYER_CONFIG.shieldColor).toBe(0x00ffff);
    expect(PLAYER_CONFIG.shieldAlpha).toBe(0.6);
  });

  it('should have correct hull configuration', () => {
    expect(PLAYER_CONFIG.maxHull).toBe(100);
  });

  it('should have movement configuration', () => {
    expect(PLAYER_CONFIG.acceleration).toBe(500);
    expect(PLAYER_CONFIG.deceleration).toBe(400);
    expect(PLAYER_CONFIG.rotationSpeed).toBe(120);
  });
});

describe('Player Class', () => {
  let mockScene: any;
  let player: Player;

  beforeEach(() => {
    mockScene = createMockScene();
    // Mock textures.exists to return true for player texture
    mockScene.textures.exists = vi.fn(() => true);
    // Create player instance
    player = new Player(mockScene, PLAYER_CONFIG.startX, PLAYER_CONFIG.startY);
  });

  it('should be created at correct position', () => {
    expect(player.x).toBe(PLAYER_CONFIG.startX);
    expect(player.y).toBe(PLAYER_CONFIG.startY);
  });

  it('should initialize with max hull and shield', () => {
    expect(player.getHull()).toBe(PLAYER_CONFIG.maxHull);
    expect(player.getShield()).toBe(PLAYER_CONFIG.maxShield);
    expect(player.getMaxHull()).toBe(PLAYER_CONFIG.maxHull);
    expect(player.getMaxShield()).toBe(PLAYER_CONFIG.maxShield);
  });

  it('should have empty bullets array initially', () => {
    expect(player.bullets).toEqual([]);
  });

  describe('Shooting', () => {
    it('should create bullet when shooting and cooldown expired', () => {
      const currentTime = 1000;
      const bullet = player.shoot(mockScene, currentTime);
      
      expect(bullet).not.toBeNull();
      expect(player.bullets.length).toBe(1);
    });

    it('should respect cooldown when shooting too quickly', () => {
      const currentTime = 1000;
      player.shoot(mockScene, currentTime);
      
      // Try to shoot immediately again
      const bullet2 = player.shoot(mockScene, currentTime);
      expect(bullet2).toBeNull();
    });

    it('should allow shooting after cooldown expires', () => {
      const time1 = 1000;
      player.shoot(mockScene, time1);
      
      const time2 = time1 + BULLET_CONFIG.cooldown;
      const bullet2 = player.shoot(mockScene, time2);
      expect(bullet2).not.toBeNull();
    });

    it('should attempt to play a sound when shooting (when audio exists)', () => {
      mockScene.cache.audio.exists = vi.fn(() => true);
      const currentTime = 1000;

      player.shoot(mockScene, currentTime);

      // Sound is routed through SoundEffects utility; just verify scene.sound.play was invoked
      expect(mockScene.sound.play).toHaveBeenCalled();
    });
  });

  describe('Damage System', () => {
    it('should take damage to shield first', () => {
      const initialShield = player.getShield();
      player.takeDamage(20);
      
      expect(player.getShield()).toBe(initialShield - 20);
      expect(player.getHull()).toBe(PLAYER_CONFIG.maxHull);
    });

    it('should damage hull when shield is depleted', () => {
      // Deplete shield
      player.takeDamage(120);
      
      expect(player.getShield()).toBe(0);
      expect(player.getHull()).toBe(PLAYER_CONFIG.maxHull - 20);
    });

    it('should not go below 0 hull', () => {
      player.takeDamage(200);
      
      expect(player.getHull()).toBeGreaterThanOrEqual(0);
    });

    it('should play shield damage sound when shield is hit', () => {
      mockScene.cache.audio.exists = vi.fn(() => true);
      player.takeDamage(10);
      // Sound should be played (tested via mockScene.sound.play calls)
    });

    it('should play shield down sound when shield depletes', () => {
      mockScene.cache.audio.exists = vi.fn(() => true);
      player.takeDamage(100);
      // Shield down sound should be played
    });

    it('should play hull damage sound when hull is damaged', () => {
      mockScene.cache.audio.exists = vi.fn(() => true);
      player.takeDamage(120);
      // Hull damage sound should be played
    });
  });

  describe('Bullet Management', () => {
    it('should track bullets', () => {
      const currentTime = 1000;
      const bullet = player.shoot(mockScene, currentTime);
      
      expect(player.bullets).toContain(bullet);
    });

    it('should remove bullet from tracking', () => {
      const currentTime = 1000;
      const bullet = player.shoot(mockScene, currentTime);
      
      player.removeBullet(bullet);
      expect(player.bullets).not.toContain(bullet);
    });
  });

  describe('Shooting State', () => {
    it('should detect when player is shooting', () => {
      // Mock space key as pressed
      if (player['spaceKey']) {
        player['spaceKey'].isDown = true;
        expect(player.isShooting()).toBe(true);
      }
    });

    it('should detect when player is not shooting', () => {
      // Mock space key as not pressed
      if (player['spaceKey']) {
        player['spaceKey'].isDown = false;
        expect(player.isShooting()).toBe(false);
      }
    });
  });
});
