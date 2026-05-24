/**
 * ScoreManager - Manages saving and loading high scores
 * Uses localStorage to persist scores across sessions
 */

export interface ScoreEntry {
  name: string;
  score: number;
  date: string; // ISO date string
}

const STORAGE_KEY = 'spaceShooterHighScores';
const MAX_SCORES = 5; // Keep top 5 scores
const MAX_NAME_LENGTH = 5;

/**
 * Save a score entry
 * Saves to localStorage and attempts to save to file via API
 */
export function saveScore(name: string, score: number): void {
  // Validate and trim name
  let trimmedName = name.trim().substring(0, MAX_NAME_LENGTH).toUpperCase();
  
  // Ensure name is not empty
  if (!trimmedName) {
    trimmedName = 'PLAYER';
  }

  const entry: ScoreEntry = {
    name: trimmedName,
    score: score,
    date: new Date().toISOString(),
  };

  // Get existing scores from cache (loaded from file)
  const scores = [...scoresCache];

  // Add new score
  scores.push(entry);

  // Sort by score (highest first)
  scores.sort((a, b) => b.score - a.score);

  // Keep only top scores
  const topScores = scores.slice(0, MAX_SCORES);
  
  // Update cache
  scoresCache = topScores;

  // Save to localStorage (always works)
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(topScores));
    console.log('✅ Score saved to localStorage:', entry);
  } catch (error) {
    console.error('Failed to save score to localStorage:', error);
  }

  // Attempt to save to file via API endpoint (may not exist, that's OK)
  saveScoresToFile(topScores).catch((error) => {
    // Silently fail - localStorage is the backup
    console.log('⚠️ Could not save to file (this is normal if no API exists):', error.message);
  });
}

/**
 * Attempt to save scores to file via API endpoint
 * This will only work if you have a server-side endpoint set up
 */
async function saveScoresToFile(scores: ScoreEntry[]): Promise<void> {
  try {
    // Try to POST to an API endpoint that saves to scores.json
    const response = await fetch('/api/scores', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(scores),
    });

    if (response.ok) {
      console.log('✅ Scores saved to file via API');
    } else {
      throw new Error(`API returned status ${response.status}`);
    }
  } catch (error) {
    // If API doesn't exist, that's fine - localStorage is the backup
    throw error;
  }
}

// Cache for scores loaded from file (not localStorage)
let scoresCache: ScoreEntry[] = [];

/**
 * Get all saved scores - returns cached scores from file (not localStorage)
 */
export function getScores(): ScoreEntry[] {
  return scoresCache;
}

/**
 * Initialize scores from file on first load, merging with localStorage
 * File is the source of truth, localStorage is merged in
 */
export async function initializeScores(): Promise<void> {
  let fileScores: ScoreEntry[] = [];
  
  try {
    console.log('📂 Loading scores from /scores.json...');
    const response = await fetch('/scores.json');
    console.log('📡 Fetch response status:', response.status);
    
    if (response.ok) {
      fileScores = await response.json() as ScoreEntry[];
      console.log('✅ Scores loaded from file:', fileScores);
    } else {
      console.warn('⚠️ Scores file not found (status:', response.status, ')');
    }
  } catch (error) {
    console.warn('⚠️ Failed to load scores file:', error);
  }

  // Load scores from localStorage (user's saved scores)
  let localStorageScores: ScoreEntry[] = [];
  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) {
      localStorageScores = JSON.parse(stored) as ScoreEntry[];
      console.log('✅ Scores loaded from localStorage:', localStorageScores);
    }
  } catch (error) {
    console.warn('⚠️ Failed to load scores from localStorage:', error);
  }

  // Merge: file is source of truth, but merge with localStorage (file takes priority for duplicates)
  // Combine both, sort by score, keep top MAX_SCORES
  // Use a Map to deduplicate by score+name (file entries override localStorage)
  const scoreMap = new Map<string, ScoreEntry>();
  
  // Add localStorage scores first (lower priority)
  for (const score of localStorageScores) {
    const key = `${score.name}-${score.score}`;
    scoreMap.set(key, score);
  }
  
  // Add file scores second (higher priority - will override localStorage)
  for (const score of fileScores) {
    const key = `${score.name}-${score.score}`;
    scoreMap.set(key, score);
  }
  
  const allScores = Array.from(scoreMap.values());
  allScores.sort((a, b) => b.score - a.score);
  scoresCache = allScores.slice(0, MAX_SCORES);

  // Update localStorage with merged scores (for persistence)
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(scoresCache));
    console.log('✅ Scores initialized:', scoresCache);
  } catch (error) {
    console.error('⚠️ Failed to save scores to localStorage:', error);
  }
}

/**
 * Clear all scores (for testing/reset)
 */
export function clearScores(): void {
  scoresCache = [];
  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch (error) {
    console.error('Failed to clear scores:', error);
  }
}

/**
 * Export scores to a downloadable JSON file
 */
export function exportScores(): void {
  try {
    const scores = getScores();
    const dataStr = JSON.stringify(scores, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `space-shooter-scores-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Failed to export scores:', error);
    alert('Failed to export scores. Please try again.');
  }
}

/**
 * Import scores from a JSON file
 */
export function importScores(file: File): Promise<boolean> {
  return new Promise((resolve) => {
    const reader = new FileReader();
    
    reader.onload = (e) => {
      try {
        const content = e.target?.result as string;
        const importedScores = JSON.parse(content) as ScoreEntry[];
        
        // Validate imported data
        if (!Array.isArray(importedScores)) {
          throw new Error('Invalid file format');
        }
        
        // Validate each entry
        const validScores = importedScores.filter(entry => 
          entry && 
          typeof entry.name === 'string' && 
          typeof entry.score === 'number' &&
          typeof entry.date === 'string'
        );
        
        if (validScores.length === 0) {
          throw new Error('No valid scores found in file');
        }
        
        // Merge with existing scores
        const existingScores = getScores();
        const allScores = [...existingScores, ...validScores];
        
        // Sort and keep top 10
        allScores.sort((a, b) => b.score - a.score);
        const topScores = allScores.slice(0, MAX_SCORES);
        
        // Update cache
        scoresCache = topScores;
        
        // Save backup to localStorage (browsers can't write to server files)
        localStorage.setItem(STORAGE_KEY, JSON.stringify(topScores));
        
        resolve(true);
      } catch (error) {
        console.error('Failed to import scores:', error);
        alert('Failed to import scores. Please check the file format.');
        resolve(false);
      }
    };
    
    reader.onerror = () => {
      console.error('Failed to read file');
      alert('Failed to read file. Please try again.');
      resolve(false);
    };
    
    reader.readAsText(file);
  });
}

/**
 * Check if a score would make it into the top scores
 */
export function isHighScore(score: number): boolean {
  const scores = getScores();
  if (scores.length < MAX_SCORES) {
    return true; // Not enough scores yet, always a high score
  }
  // Check if score is higher than the lowest top score
  const lowestScore = scores[scores.length - 1]?.score || 0;
  return score > lowestScore;
}

