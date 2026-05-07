# Cutscene System

A flexible cutscene system for playing scripted sequences in the game.

## Overview

The cutscene system consists of:
- **BaseCutscene**: Abstract base class that all cutscenes extend
- **CutsceneManager**: Manages cutscene playback in a scene
- **DialogueCutscene**: Concrete implementation for dialogue sequences

## Usage Example

### Basic Setup in a Scene

```typescript
import { CutsceneManager, DialogueCutscene } from './cutscenes';

export class MyScene extends Phaser.Scene {
  private cutsceneManager?: CutsceneManager;

  create() {
    // Initialize cutscene manager
    this.cutsceneManager = new CutsceneManager(this);
  }

  update(time: number, delta: number) {
    // Update cutscene manager
    if (this.cutsceneManager) {
      this.cutsceneManager.update(time, delta);
    }
  }
}
```

### Playing a Dialogue Cutscene

```typescript
const dialogue = new DialogueCutscene(this, [
  { 
    text: "We've detected enemy ships approaching!", 
    speaker: "Commander",
    color: "#00ff00",
    duration: 3000
  },
  { 
    text: "All hands to battle stations!", 
    speaker: "Commander",
    color: "#00ff00",
    duration: 2500
  },
  { 
    text: "This is it, pilot. Good luck out there.", 
    speaker: "Commander",
    color: "#00ff00",
    duration: 3000
  },
]);

this.cutsceneManager?.playCutscene(dialogue);
```

### Creating Custom Cutscenes

Extend `BaseCutscene` to create your own cutscene types:

```typescript
import { BaseCutscene } from './BaseCutscene';

export class MyCustomCutscene extends BaseCutscene {
  constructor(scene: Phaser.Scene) {
    super(scene);
    this.canExit = true; // Allow skipping
  }

  play(): void {
    this.isPlaying = true;
    // Your cutscene logic here
  }

  exit(): void {
    // Handle exit/skip
    this.complete();
  }

  update(time: number, delta: number): void {
    // Update logic (called every frame)
  }

  destroy(): void {
    // Clean up resources
  }

  private complete(): void {
    this.isComplete = true;
    this.isPlaying = false;
  }
}
```

## Cutscene Properties

- **canExit**: Whether the cutscene can be skipped (Space/Tap)
- **isPlaying**: Whether the cutscene is currently playing
- **isComplete**: Whether the cutscene has finished

## Exit Conditions

Cutscenes can be exited:
1. Automatically when `isComplete` becomes true
2. Manually by calling `exit()` if `canExit` is true
3. By user input (Space/Tap) if `canExit` is true

## Triggering Cutscenes

Cutscenes can be triggered at any point in your game logic:

- When a level starts
- Before/after boss fights
- When reaching certain scores
- When entering specific areas
- In response to player actions

Example:
```typescript
// In GameScene.update()
if (this.score >= 50000 && !this.bossIntroPlayed) {
  this.bossIntroPlayed = true;
  const bossIntro = new DialogueCutscene(this, [
    { text: "Warning! Boss detected!", color: "#ff0000" }
  ]);
  this.cutsceneManager?.playCutscene(bossIntro);
}
```

