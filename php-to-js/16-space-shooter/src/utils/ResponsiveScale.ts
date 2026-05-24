import Phaser from 'phaser';
import { GAME_CONFIG } from '../config';

/**
 * Utility for responsive scaling based on actual display size
 * This ensures all game elements scale properly regardless of screen size or aspect ratio
 */

/**
 * Get the scale factor based on the actual display dimensions
 * Returns a multiplier that can be applied to sizes
 * Includes a minimum scale to prevent elements from being too small on small devices
 */
export function getDisplayScale(scene: Phaser.Scene): number {
  const displaySize = scene.scale.displaySize;
  const baseWidth = GAME_CONFIG.width; // 800
  const baseHeight = GAME_CONFIG.height; // 600
  
  // Use the smaller scale factor to maintain aspect ratio
  const scaleX = displaySize.width / baseWidth;
  const scaleY = displaySize.height / baseHeight;
  
  // Use the minimum to ensure everything fits
  // This prevents elements from being too large on wide screens
  let scale = Math.min(scaleX, scaleY);
  
  // Apply minimum scale to prevent things from being too small on small devices
  // Minimum of 1.0 means elements won't be smaller than their base size
  const MIN_SCALE = 1.0;
  scale = Math.max(scale, MIN_SCALE);
  
  return scale;
}

/**
 * Scale a dimension value based on display size
 */
export function scaleSize(scene: Phaser.Scene, baseSize: number): number {
  return baseSize * getDisplayScale(scene);
}

/**
 * Get responsive dimensions for width and height
 */
export function getResponsiveSize(scene: Phaser.Scene, width: number, height: number): { width: number; height: number } {
  const scale = getDisplayScale(scene);
  return {
    width: width * scale,
    height: height * scale,
  };
}

