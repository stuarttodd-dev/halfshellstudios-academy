// ES Modules (ESM) — the standard module system in modern JavaScript.
// Named exports let callers import exactly what they need.
// A default export is the "main thing" a module provides.
//
// Usage elsewhere:
//   import greet, { add, multiply } from './modules-esm.mjs';
//
// Run this file directly:
//   node snippets/modules-esm.mjs

export const add = (a, b) => a + b;
export const multiply = (a, b) => a * b;

export default function greet(name) {
  return `Hello, ${name}`;
}

// Self-test so the file is runnable standalone
console.log('add(2, 3):', add(2, 3));         // 5
console.log('multiply(4, 5):', multiply(4, 5)); // 20
console.log(greet('PHP dev'));                 // Hello, PHP dev

// PHP comparison:
// ESM named exports ≈ public functions in a PHP file loaded with use/import.
// Default export ≈ the primary class in a PHP file (one class per file convention).
// Key difference: ESM is statically analysed at parse time; PHP includes are runtime.
