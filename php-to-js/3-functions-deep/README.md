# Chapter 3 — Functions Deep

This chapter digs into JavaScript's treatment of functions as first-class values: closures, `this` binding, higher-order functions, and functional composition patterns. PHP developers will find some parallels with PHP's own closures and callable types, but the `this` rules are unique to JS.

## Chapter project

A small utility library — `format`, `pick`, `groupBy`, `compose`, and `pipe` — followed by a working demo against a fake orders array.

```bash
node chapter-project/index.js
```

## Snippets

```bash
node snippets/closures.js
node snippets/this-four-rules.js
```

| File | What it demonstrates |
|------|----------------------|
| `snippets/closures.js` | Closure counter, stale-closure trap with `var`, fix with `let` |
| `snippets/this-four-rules.js` | Default, implicit, explicit, and `new` binding + arrow functions |
