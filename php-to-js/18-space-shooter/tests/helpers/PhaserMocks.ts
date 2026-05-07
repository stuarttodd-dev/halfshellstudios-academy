/**
 * Mock helpers for Phaser testing.
 *
 * Two exports:
 *  - createMockScene()  — a plain object that satisfies the Phaser.Scene API
 *    used by entity constructors and game-logic classes.
 *  - mockPhaserModule   — pass this to vi.mock('phaser', () => mockPhaserModule)
 *    to prevent Phaser's CanvasFeatures code from running under jsdom.
 */

import { vi } from 'vitest';

// ─── Scene mock ───────────────────────────────────────────────────────────────

export function createMockScene(): any {
  return {
    add: {
      existing: vi.fn(),
      graphics: vi.fn(() => ({
        fillStyle: vi.fn().mockReturnThis(),
        fillRect: vi.fn().mockReturnThis(),
        beginPath: vi.fn().mockReturnThis(),
        moveTo: vi.fn().mockReturnThis(),
        lineTo: vi.fn().mockReturnThis(),
        closePath: vi.fn().mockReturnThis(),
        fillPath: vi.fn().mockReturnThis(),
        generateTexture: vi.fn(),
        destroy: vi.fn(),
        clear: vi.fn().mockReturnThis(),
        strokeCircle: vi.fn().mockReturnThis(),
        lineStyle: vi.fn().mockReturnThis(),
        strokeRect: vi.fn().mockReturnThis(),
      })),
      text: vi.fn(() => ({
        setOrigin: vi.fn().mockReturnThis(),
        setDepth: vi.fn().mockReturnThis(),
        setPosition: vi.fn().mockReturnThis(),
        setText: vi.fn().mockReturnThis(),
        setAlpha: vi.fn().mockReturnThis(),
        setVisible: vi.fn().mockReturnThis(),
        setInteractive: vi.fn().mockReturnThis(),
        on: vi.fn().mockReturnThis(),
        destroy: vi.fn(),
        x: 0,
        y: 0,
        alpha: 1,
        visible: true,
      })),
      circle: vi.fn(() => ({
        setDepth: vi.fn().mockReturnThis(),
        setFillStyle: vi.fn().mockReturnThis(),
        setStrokeStyle: vi.fn().mockReturnThis(),
        setPosition: vi.fn().mockReturnThis(),
        setAlpha: vi.fn().mockReturnThis(),
        destroy: vi.fn(),
        x: 0,
        y: 0,
      })),
      rectangle: vi.fn(() => ({
        setDepth: vi.fn().mockReturnThis(),
        setFillStyle: vi.fn().mockReturnThis(),
        setPosition: vi.fn().mockReturnThis(),
        setAlpha: vi.fn().mockReturnThis(),
        setOrigin: vi.fn().mockReturnThis(),
        setDisplaySize: vi.fn().mockReturnThis(),
        setSize: vi.fn().mockReturnThis(),
        destroy: vi.fn(),
        x: 0,
        y: 0,
        width: 30,
        height: 4,
      })),
      image: vi.fn(() => ({
        setDisplaySize: vi.fn().mockReturnThis(),
        setDepth: vi.fn().mockReturnThis(),
        setPosition: vi.fn().mockReturnThis(),
        setAlpha: vi.fn().mockReturnThis(),
        x: 0,
        y: 0,
      })),
      particles: vi.fn(() => ({
        setDepth: vi.fn().mockReturnThis(),
        destroy: vi.fn(),
      })),
      container: vi.fn(() => ({
        add: vi.fn().mockReturnThis(),
        setScale: vi.fn().mockReturnThis(),
        x: 0,
        y: 0,
      })),
    },
    physics: {
      add: {
        existing: vi.fn(),
        overlap: vi.fn(),
        collider: vi.fn(),
      },
    },
    input: {
      keyboard: {
        createCursorKeys: vi.fn(() => ({
          up: { isDown: false },
          down: { isDown: false },
          left: { isDown: false },
          right: { isDown: false },
        })),
        addKey: vi.fn(() => ({ isDown: false })),
        once: vi.fn(),
      },
      on: vi.fn(),
      once: vi.fn(),
    },
    time: {
      now: 0,
      delayedCall: vi.fn(),
    },
    tweens: {
      add: vi.fn(() => ({ on: vi.fn() })),
    },
    sound: {
      add: vi.fn(() => ({
        play: vi.fn(),
        stop: vi.fn(),
        destroy: vi.fn(),
      })),
      play: vi.fn(),
      unlock: vi.fn(),
      locked: false,
    },
    textures: {
      exists: vi.fn(() => true),
      get: vi.fn(() => ({
        get: vi.fn(() => ({ width: 128, height: 128, name: '__BASE' })),
      })),
    },
    cache: {
      audio: {
        exists: vi.fn(() => false),
      },
    },
    scale: {
      width: 800,
      height: 600,
      displaySize: { width: 800, height: 600 },
    },
    children: {
      exists: vi.fn(() => true),
    },
    load: {
      on: vi.fn(),
      image: vi.fn(),
      audio: vi.fn(),
    },
    cameras: {
      main: {
        shake: vi.fn(),
        flash: vi.fn(),
      },
    },
    scene: {
      start: vi.fn(),
      stop: vi.fn(),
    },
  };
}

// ─── Phaser module mock ───────────────────────────────────────────────────────
//
// Use with: vi.mock('phaser', () => mockPhaserModule)
//
// This prevents Phaser's CanvasFeatures.js from running under jsdom, which
// fails because jsdom does not implement HTMLCanvasElement.getContext().

class MockSprite {
  x: number;
  y: number;
  angle: number = 0;
  alpha: number = 1;
  visible: boolean = true;
  active: boolean = true;
  displayWidth: number = 64;
  displayHeight: number = 64;
  width: number = 64;
  height: number = 64;
  scene: any;
  body: any;

  constructor(scene: any, x: number, y: number, _texture?: string) {
    this.x = x;
    this.y = y;
    this.scene = scene;
    this.body = {
      setVelocity: vi.fn(),
      setVelocityX: vi.fn(),
      setVelocityY: vi.fn(),
      setSize: vi.fn(),
      setOffset: vi.fn(),
      setBounce: vi.fn(),
      velocity: { x: 0, y: 0 },
      x: x,
      y: y,
    };
    if (scene?.physics?.add?.existing) {
      scene.physics.add.existing(this);
    }
    if (scene?.add?.existing) {
      scene.add.existing(this);
    }
  }

  setPosition(x: number, y: number) { this.x = x; this.y = y; return this; }
  setDisplaySize(w: number, h: number) { this.displayWidth = w; this.displayHeight = h; return this; }
  setSize(w: number, h: number) { this.width = w; this.height = h; return this; }
  setDepth() { return this; }
  setOrigin() { return this; }
  setAlpha(a: number) { this.alpha = a; return this; }
  setVisible(v: boolean) { this.visible = v; return this; }
  setTint() { return this; }
  clearTint() { return this; }
  setTexture() { return this; }
  setScale() { return this; }
  setInteractive() { return this; }
  setFlipX() { return this; }
  setAngle(a: number) { this.angle = a; return this; }
  setFrame() { return this; }
  setVelocity() { return this; }
  setVelocityX() { return this; }
  setVelocityY() { return this; }
  setDrag() { return this; }
  setDragX() { return this; }
  setDragY() { return this; }
  setBounce() { return this; }
  setCollideWorldBounds() { return this; }
  setMaxVelocity() { return this; }
  setRotation(_r: number) { return this; }
  setMass() { return this; }
  setPipeline() { return this; }
  play() { return this; }
  on() { return this; }
  once() { return this; }
  emit() { return this; }
  destroy() { this.active = false; }
  setActive(val: boolean) { this.active = val; return this; }
  getData(_key: string) { return undefined; }
  setData(_key: string, _val: any) { return this; }
}

export const mockPhaserModule = {
  default: {
    Physics: {
      Arcade: {
        Sprite: MockSprite,
      },
    },
    Math: {
      Between: vi.fn((min: number, max: number) => Math.floor(Math.random() * (max - min + 1)) + min),
      Clamp: (value: number, min: number, max: number) => Math.min(Math.max(value, min), max),
      DegToRad: (degrees: number) => degrees * (Math.PI / 180),
      RadToDeg: (radians: number) => radians * (180 / Math.PI),
      FloatBetween: (min: number, max: number) => Math.random() * (max - min) + min,
      Distance: {
        Between: (x1: number, y1: number, x2: number, y2: number) =>
          Math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2),
      },
      Angle: {
        Between: vi.fn((_x1: number, _y1: number, _x2: number, _y2: number) => 0),
        ToDegrees: (r: number) => r * (180 / Math.PI),
        Wrap: (a: number) => a,
      },
      Vector2: class {
        x: number; y: number;
        constructor(x = 0, y = 0) { this.x = x; this.y = y; }
        normalize() { return this; }
        scale() { return this; }
      },
    },
    GameObjects: {
      Sprite: MockSprite,
      Image: MockSprite,
    },
    Input: {
      Keyboard: {
        KeyCodes: {
          W: 87, A: 65, S: 83, D: 68,
          UP: 38, DOWN: 40, LEFT: 37, RIGHT: 39,
          SPACE: 32, SHIFT: 16,
        },
        JustDown: vi.fn(() => false),
        JustUp: vi.fn(() => false),
      },
    },
    Scene: class {},
    AUTO: 0,
  },
};
