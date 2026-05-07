// ESM (modern — use this in new code):
//   import { foo } from './foo.js';       // named import
//   import bar from './bar.js';           // default import
//   export const baz = 1;                 // named export
//   export default function() {}          // default export
//   await import('./lazy.js')             // dynamic import

// CJS (legacy Node.js — you'll see this in older packages):
//   const { foo } = require('./foo');     // named-ish
//   const bar = require('./bar');         // default-ish
//   module.exports = { baz: 1 };         // export
//   exports.baz = 1;                     // add to existing exports

// Key differences:
// ESM: static (parsed before execution), tree-shakeable, async
// CJS: dynamic (require() runs at call time), not tree-shakeable

// In package.json:
// "type": "module"    → .js files are ESM
// "type": "commonjs"  → .js files are CJS  (default when "type" is absent)
// .mjs always ESM, .cjs always CJS, regardless of package.json

console.log('ESM file — import.meta.url:', import.meta.url);
