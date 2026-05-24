import { SCENE_KEYS } from '../../config';
import { BaseCutscene, CutsceneTransition } from './BaseCutscene';

/**
 * Cutscene1Scene - First cutscene after the opening crawl
 * Shows scene1a through scene1e images with timing
 */
export class Cutscene1Scene extends BaseCutscene {
  constructor() {
    super({ key: SCENE_KEYS.CUTSCENE_1 });
  }

  /**
   * Define the transitions for this cutscene
   * Total duration: ~90 seconds (including fade transitions)
   * Text typing will finish at the end of each slide's duration
   */
  protected getTransitions(): CutsceneTransition[] {
    return [
      { 
        backgroundKey: 'scene1a', 
        text: '"Every swing of the pick buys me another breath.\n\nThey call this work.\n\nI call it surviving long enough to hate tomorrow."',
        duration: 9000, // Total slide time - typing finishes at end
        typingSpeed: 30 // Characters per second
      },
      { 
        backgroundKey: 'scene1b', 
        text: '"He\'s bleeding out on dirt and they\'re shouting like we broke a rule.\n\nFunny thing is—we stopped caring about rules a long time ago."',
        duration: 9000, // Total slide time - typing finishes at end
        typingSpeed: 30
      },
      { 
        backgroundKey: 'scene1c', 
        text: '"He looks at me and smiles.\n\nThat\'s when I know.\n\nRun isn\'t a choice anymore—it\'s an order."',
        duration: 9000, // Total slide time - typing finishes at end
        typingSpeed: 30
      },
      { 
        backgroundKey: 'scene1d', 
        text: '"I don\'t know how to fly this thing.\n\nDoesn\'t matter.\n\nFreedom only needs one direction: away.\n\nAnd besides, it\'s all in the reflexes."',
        duration: 9000, // Total slide time - typing finishes at end
        typingSpeed: 30
      },
      { 
        backgroundKey: 'scene1e', 
        text: '"They can chase the ship.\n\nThey can\'t chase when.\n\nGood luck finding me in yesterday.\n\nMaybe if I go back to the beginning of all this I can stop it ever happening..."',
        duration: 9000, // Total slide time - typing finishes at end
        typingSpeed: 30
      },
    ];
    // Each slide: ~9 seconds (includes fade in 500ms + typing time + buffer)
    // Total: ~45 seconds (5 slides × 9s)
  }

  /**
   * Get the background music for this cutscene
   */
  protected getMusicKey(): string | undefined {
    return 'cutscene1';
  }

  /**
   * Get the scene to transition to when complete
   */
  protected getExitSceneKey(): string {
    return SCENE_KEYS.GAME;
  }
}

