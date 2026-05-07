# Chapter 9 — State and Data Flow

State management in plain JavaScript: single source of truth, derived state, pub/sub events, and immutable updates.

## Run the chapter project

```bash
node chapter-project/index.js
```

No dependencies required.

## Files

| File | What it shows |
|------|---------------|
| `chapter-project/index.js` | Pokédex — tabs + filter + counter, single state object, pure render function |
| `snippets/pub-sub.js` | Minimal EventEmitter: `on`, `off`, `emit`, with unsubscribe return value |
| `snippets/immutability.js` | Spread-based immutable updates for objects and arrays |

## Key ideas

- Store state in one place — never scatter it across the page
- Compute derived values (filtered lists, totals) on the fly — don't cache what you can recalculate
- Mutations flow through named functions, never directly
- This pattern is the foundation that Vue/React/Redux formalise
