# Chapter 1 — JS Fundamentals

This chapter covers the runtime model and core language behaviours that trip up PHP developers moving to JavaScript: the event loop, variable scoping and hoisting, ES modules, and type coercion.

No chapter project was defined for this chapter. The snippets below each illustrate one key concept and are designed to run directly with Node.

## Snippets

| File | What it demonstrates |
|------|----------------------|
| `snippets/event-loop.js` | Sync → microtask → macrotask execution order |
| `snippets/var-let-const.js` | `var` hoisting vs `let` TDZ vs `const` rebinding |
| `snippets/modules-esm.mjs` | Named exports, default export, ESM syntax |
| `snippets/type-coercion.js` | Loose equality gotchas vs PHP 8 comparison rules |

## How to run

```bash
node snippets/event-loop.js
node snippets/var-let-const.js
node snippets/modules-esm.mjs
node snippets/type-coercion.js
```
