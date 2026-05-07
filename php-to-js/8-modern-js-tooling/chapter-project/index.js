// Demonstrates ESM — requires "type": "module" in package.json
// Run: node index.js

// Named imports from a local module
import { formatCurrency, slugify } from './utils.js';

// Dynamic import — loads only when needed
const { default: heavy } = await import('./heavy-module.js');

console.log(formatCurrency(2999));
console.log(slugify('PHP to JavaScript: A Deep Dive'));
heavy.run();
