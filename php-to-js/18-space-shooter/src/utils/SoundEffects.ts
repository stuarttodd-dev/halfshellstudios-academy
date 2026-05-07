/**
 * Sound Effects utility
 * Plays loaded sound effects using Phaser's sound system
 */

export class SoundEffects {
  private scene?: Phaser.Scene;

  /**
   * Initialize with a scene (needed to access Phaser sound system)
   */
  setScene(scene: Phaser.Scene): void {
    this.scene = scene;
  }

  /**
   * Helper to play a sound effect
   */
  private playSound(key: string, volume: number = 0.7): void {
    try {
      if (!this.scene || !this.scene.sound) {
        console.warn(`Cannot play ${key}: no scene or sound system`);
        return;
      }
      
      // Check if sound exists in cache
      if (!this.scene.cache.audio.exists(key)) {
        console.warn(`Sound ${key} not found in cache`);
        return;
      }
      
      // Play the sound
      this.scene.sound.play(key, { volume });
      console.log(`🔊 Playing sound: ${key}`);
    } catch (e) {
      console.warn(`Error playing sound ${key}:`, e);
    }
  }

  /**
   * Play shield damage sound
   */
  playShieldDamage(): void {
    this.playSound('shield_damage', 0.175);
  }

  /**
   * Play enemy damage sound - removed (bullets hitting enemies shouldn't play shield damage sound)
   */
  playEnemyDamage(): void {
    // No sound for bullets hitting enemies - only visual flash
  }

  /**
   * Play hull damage sound
   */
  playHullDamage(): void {
    this.playSound('hull_damage', 0.175);
  }

  /**
   * Play shield down sound
   */
  playShieldDown(): void {
    this.playSound('shield_down', 0.2);
  }

  /**
   * Play explosion sound
   */
  playExplosion(): void {
    this.playSound('explosion', 0.2);
  }

  /**
   * Play shooting sound (player fires bullet)
   */
  playShoot(): void {
    // Try to play a dedicated shoot sound, or use shield_damage at low volume as fallback
    if (this.scene && this.scene.cache.audio.exists('shoot')) {
      this.playSound('shoot', 0.125);
    } else if (this.scene && this.scene.cache.audio.exists('shield_damage')) {
      // Use shield_damage at very low volume as temporary shooting sound
      this.playSound('shield_damage', 0.075);
    }
  }
}

// Export singleton instance
export const soundEffects = new SoundEffects();

