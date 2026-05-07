import Phaser from 'phaser';
// Note: DialogueCutscene uses old pattern - needs refactoring to work with new BaseCutscene Scene system
// import { BaseCutscene } from './BaseCutscene';
import { scaleSize } from '../../utils/ResponsiveScale';

export interface DialogueLine {
  text: string;
  speaker?: string; // Optional speaker name
  duration?: number; // Duration in ms (default: 3000)
  color?: string; // Text color (default: '#ffffff')
}

/**
 * DialogueCutscene - Plays a sequence of dialogue lines
 * 
 * Example usage:
 * const cutscene = new DialogueCutscene(scene, [
 *   { text: "We've detected enemy ships!", speaker: "Commander", color: "#00ff00" },
 *   { text: "All hands to battle stations!", speaker: "Commander", color: "#00ff00" },
 * ]);
 * cutsceneManager.playCutscene(cutscene);
 */
// TODO: Refactor to work with new Scene-based BaseCutscene
// This class uses the old pattern and needs to be refactored
export class DialogueCutscene {
  private scene: Phaser.Scene;
  private dialogueLines: DialogueLine[];
  private currentLineIndex: number = 0;
  private dialogueContainer?: Phaser.GameObjects.Container;
  private nextLineTimer?: Phaser.Time.TimerEvent;
  private isPlaying: boolean = false;
  private _isComplete: boolean = false;
  private canExit: boolean = true;

  constructor(scene: Phaser.Scene, dialogueLines: DialogueLine[]) {
    this.scene = scene;
    this.dialogueLines = dialogueLines;
    this.canExit = true; // Can skip dialogue
  }

  getIsPlaying(): boolean { return this.isPlaying; }
  getIsComplete(): boolean { return this._isComplete; }
  getCanExit(): boolean { return this.canExit; }

  play(): void {
    if (this.isPlaying) return;
    
    this.isPlaying = true;
    this._isComplete = false;
    this.currentLineIndex = 0;
    
    this.showCurrentLine();
  }

  private showCurrentLine(): void {
    if (this.currentLineIndex >= this.dialogueLines.length) {
      this.complete();
      return;
    }

    const line = this.dialogueLines[this.currentLineIndex];
    const { width, height } = this.scene.scale;

    // Clean up previous line
    if (this.dialogueContainer) {
      this.dialogueContainer.destroy();
    }

    // Create container for dialogue
    const container = this.scene.add.container(width / 2, height - scaleSize(this.scene, 150));
    container.setDepth(100);

    // Background panel
    const bgWidth = scaleSize(this.scene, 600);
    const bgHeight = scaleSize(this.scene, 120);
    const bg = this.scene.add.rectangle(0, 0, bgWidth, bgHeight, 0x000000, 0.8);
    bg.setStrokeStyle(2, 0xffffff, 1);
    container.add(bg);

    // Speaker name (if provided)
    if (line.speaker) {
      const speakerStyle: Phaser.Types.GameObjects.Text.TextStyle = {
        fontSize: `${scaleSize(this.scene, 20)}px`,
        color: '#ffff00',
        fontFamily: 'Arial',
        fontStyle: 'bold',
      };
      const speaker = this.scene.add.text(
        0,
        -scaleSize(this.scene, 40),
        line.speaker + ':',
        speakerStyle
      );
      speaker.setOrigin(0.5, 0.5);
      container.add(speaker);
    }

    // Dialogue text
    const textStyle: Phaser.Types.GameObjects.Text.TextStyle = {
      fontSize: `${scaleSize(this.scene, 24)}px`,
      color: line.color || '#ffffff',
      fontFamily: 'Arial',
      align: 'center',
      wordWrap: { width: bgWidth - scaleSize(this.scene, 40) },
    };
    const text = this.scene.add.text(0, scaleSize(this.scene, 10), line.text, textStyle);
    text.setOrigin(0.5, 0.5);
    container.add(text);

    // Fade in
    container.setAlpha(0);
    this.scene.tweens.add({
      targets: container,
      alpha: 1,
      duration: 300,
      ease: 'Power2',
    });

    this.dialogueContainer = container;

    // Auto-advance after duration
    const duration = line.duration || 3000;
    this.nextLineTimer = this.scene.time.delayedCall(duration, () => {
      this.advanceLine();
    });
  }

  private advanceLine(): void {
    this.currentLineIndex++;
    
    // Fade out current line
    if (this.dialogueContainer) {
      this.scene.tweens.add({
        targets: this.dialogueContainer,
        alpha: 0,
        duration: 200,
        ease: 'Power2',
        onComplete: () => {
          this.showCurrentLine();
        },
      });
    } else {
      this.showCurrentLine();
    }
  }

  private complete(): void {
    if (this.dialogueContainer) {
      this.scene.tweens.add({
        targets: this.dialogueContainer,
        alpha: 0,
        duration: 300,
        ease: 'Power2',
        onComplete: () => {
          if (this.dialogueContainer) {
            this.dialogueContainer.destroy();
            this.dialogueContainer = undefined;
          }
          this._isComplete = true;
          this.isPlaying = false;
        },
      });
    } else {
      this._isComplete = true;
      this.isPlaying = false;
    }
  }

  exit(): void {
    if (!this.canExit || !this.isPlaying) return;

    // Cancel timer
    if (this.nextLineTimer) {
      this.nextLineTimer.remove();
      this.nextLineTimer = undefined;
    }

    // Complete immediately
    this.complete();
  }

  destroy(): void {
    if (this.nextLineTimer) {
      this.nextLineTimer.remove();
    }
    if (this.dialogueContainer) {
      this.dialogueContainer.destroy();
    }
    this.isPlaying = false;
  }
}

