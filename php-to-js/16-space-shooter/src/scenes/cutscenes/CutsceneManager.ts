import Phaser from 'phaser';
import { DialogueCutscene } from './DialogueCutscene';

/**
 * CutsceneManager - Manages DialogueCutscene playback within a scene.
 * Note: Scene-based cutscenes (BaseCutscene subclasses) are launched as
 * separate Phaser Scenes and do not use this manager.
 */
export class CutsceneManager {
  private scene: Phaser.Scene;
  private currentCutscene: DialogueCutscene | null = null;
  private cutsceneQueue: DialogueCutscene[] = [];

  constructor(scene: Phaser.Scene) {
    this.scene = scene;
  }

  /**
   * Play a cutscene immediately (queues if one is already playing)
   */
  playCutscene(cutscene: DialogueCutscene): void {
    if (this.currentCutscene) {
      // Queue the cutscene
      this.cutsceneQueue.push(cutscene);
    } else {
      // Play immediately
      this.startCutscene(cutscene);
    }
  }

  /**
   * Start playing a cutscene
   */
  private startCutscene(cutscene: DialogueCutscene): void {
    this.currentCutscene = cutscene;
    cutscene.play();

    // Listen for skip input
    this.setupSkipInput();
  }

  private skipHandlerKey?: Phaser.Events.EventEmitter;
  private skipHandlerPointer?: Phaser.Events.EventEmitter;

  /**
   * Setup input to skip cutscene (if allowed)
   */
  private setupSkipInput(): void {
    // Remove any existing handlers
    this.clearSkipInput();

    const skipHandler = () => {
      if (this.currentCutscene && this.currentCutscene.getCanExit()) {
        this.clearSkipInput();
        this.currentCutscene.exit();
        this.onCutsceneComplete();
      }
    };

    // Listen for spacebar or tap to skip
    this.skipHandlerKey = this.scene.input.keyboard?.on('keydown-SPACE', skipHandler);
    this.skipHandlerPointer = this.scene.input.on('pointerdown', skipHandler);
  }

  /**
   * Clear skip input handlers
   */
  private clearSkipInput(): void {
    if (this.skipHandlerKey) {
      this.scene.input.keyboard?.off('keydown-SPACE', () => {});
      this.skipHandlerKey = undefined;
    }
    if (this.skipHandlerPointer) {
      this.scene.input.off('pointerdown', () => {});
      this.skipHandlerPointer = undefined;
    }
  }

  /**
   * Called when current cutscene completes
   */
  private onCutsceneComplete(): void {
    if (this.currentCutscene) {
      this.currentCutscene.destroy();
      this.currentCutscene = null;
    }

    // Play next cutscene in queue if any
    if (this.cutsceneQueue.length > 0) {
      const nextCutscene = this.cutsceneQueue.shift();
      if (nextCutscene) {
        this.startCutscene(nextCutscene);
      }
    }
  }

  /**
   * Update cutscene (called from scene's update method)
   */
  update(_time: number, _delta: number): void {
    if (this.currentCutscene && this.currentCutscene.getIsComplete()) {
      this.onCutsceneComplete();
    }
  }

  /**
   * Check if a cutscene is currently playing
   */
  isPlaying(): boolean {
    return this.currentCutscene !== null && this.currentCutscene.getIsPlaying();
  }

  /**
   * Clear all cutscenes
   */
  clear(): void {
    this.clearSkipInput();
    if (this.currentCutscene) {
      this.currentCutscene.destroy();
      this.currentCutscene = null;
    }
    this.cutsceneQueue = [];
  }
}

