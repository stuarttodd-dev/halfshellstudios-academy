# Chapter 6 — Async JavaScript

This chapter covers JavaScript's async model: the event loop, Promises, async/await, and how to handle parallel requests and partial failures.

## Files

| Path | What it shows |
|---|---|
| `chapter-project/index.js` | Staged async flows: serial, `Promise.all`, `Promise.allSettled`, AbortController |
| `snippets/promises-basics.js` | Promise states, `.then`/`.catch`/`.finally`, async/await as sugar |
| `snippets/async-error-handling.js` | Three error-handling patterns including the Result tuple |

## Run the chapter project

```bash
node chapter-project/index.js
```

Requires Node.js v18+ (built-in `fetch`). Hits the free PokéAPI — no auth required.
