import Phaser from 'phaser';
import { soundEffects } from './SoundEffects';

/**
 * Creates an explosion effect at the given position
 * @param scene - The scene to add the explosion to
 * @param x - X position of the explosion
 * @param y - Y position of the explosion
 * @param size - Size multiplier for the explosion (default: 1)
 */
export function createExplosion(
  scene: Phaser.Scene,
  x: number,
  y: number,
  size: number = 1
): void {
  // Play explosion sound
  soundEffects.playExplosion();
  
  // Create multiple circles that expand outward for explosion effect
  const particleCount = 12;
  const baseRadius = 5 * size;
  const maxRadius = 30 * size;

  for (let i = 0; i < particleCount; i++) {
    const angle = (i / particleCount) * Math.PI * 2;
    const distance = Phaser.Math.Between(baseRadius, maxRadius);
    
    const particleX = x + Math.cos(angle) * distance;
    const particleY = y + Math.sin(angle) * distance;
    
    // Create a particle
    const particle = scene.add.circle(
      particleX,
      particleY,
      Phaser.Math.Between(3, 6) * size,
      Phaser.Math.Between(0, 1) === 0 ? 0xffff00 : 0xff6600, // Yellow or orange
      1
    );

    // Animate the explosion
    scene.tweens.add({
      targets: particle,
      alpha: 0,
      scale: 0,
      duration: 300,
      ease: 'Power2',
      onComplete: () => {
        particle.destroy();
      },
    });

    // Also expand outward
    scene.tweens.add({
      targets: particle,
      x: particleX + Math.cos(angle) * (maxRadius * 1.5),
      y: particleY + Math.sin(angle) * (maxRadius * 1.5),
      duration: 300,
      ease: 'Power2',
    });
  }

  // Add a bright flash in the center
  const flash = scene.add.circle(x, y, 10 * size, 0xffffff, 1);
  scene.tweens.add({
    targets: flash,
    radius: maxRadius * 2,
    alpha: 0,
    duration: 200,
    ease: 'Power2',
    onComplete: () => {
      flash.destroy();
    },
  });
}

