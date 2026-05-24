# Chapter 1 — JS Fundamentals

This chapter covers the runtime model and core language behaviours that trip up PHP developers moving to JavaScript: the event loop, variable scoping and hoisting, ES modules, and type coercion.

## Chapter project

Port a PHP-style `Formatter` utility to exported JavaScript functions, then log a product catalog with truncated titles, formatted prices, and slug URL paths.

| File | What it shows |
|------|---------------|
| `chapter-project/formatter.js` | `formatCurrency`, `truncate`, `slugify` as named exports |
| `chapter-project/main.js` | Product catalog demo with `try/catch` around currency formatting |

### How to run the chapter project

From the **repository root**:

```bash
node php-to-js/1-js-fundamentals/chapter-project/main.js
```

Or `cd` into this chapter first:

```bash
cd php-to-js/1-js-fundamentals
node chapter-project/main.js
```

Requires **Node.js v18+**. No `npm install` — the project uses plain ESM (`package.json` only sets `"type": "module"`).

Expected output:

```
Running shoes — Trail Ed…      £89.99  /products/running-shoes-trail-edition
Merino Wool Socks              £12.50  /products/merino-wool-socks
```

## Snippets

| File | What it demonstrates |
|------|----------------------|
| `snippets/event-loop.js` | Sync → microtask → macrotask execution order |
| `snippets/var-let-const.js` | `var` hoisting vs `let` TDZ vs `const` rebinding |
| `snippets/modules-esm.mjs` | Named exports, default export, ESM syntax |
| `snippets/type-coercion.js` | Loose equality gotchas vs PHP 8 comparison rules |

### How to run the snippets

From this chapter directory (`php-to-js/1-js-fundamentals`):

```bash
node snippets/event-loop.js
node snippets/var-let-const.js
node snippets/modules-esm.mjs
node snippets/type-coercion.js
```
