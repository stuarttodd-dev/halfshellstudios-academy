# Chapter 8 — Async JavaScript

Debounced queries, in-flight deduplication, stale-result guards, loading/error/empty states, and optional abort — built as a resilient async search module.

## Chapter project

| File | What it shows |
|------|---------------|
| `chapter-project/debounce.js` | `debounce(fn, ms)` for paused typing |
| `chapter-project/fake-search.js` | Fake API with delay and abort support |
| `chapter-project/search.js` | `executeSearch`, `latestId` guard, `render` — no fetch in render |
| `chapter-project/main.js` | Console timing demo (lesson sandbox) |

### How to run — console demo

From the **repository root**:

```bash
node php-to-js/8-async-javascript/chapter-project/main.js
```

Or `cd` into this chapter first:

```bash
cd php-to-js/8-async-javascript
node chapter-project/main.js
```

Requires **Node.js v18+**. No `npm install`.

Expected output (timing may vary slightly):

```
final { status: 'ok', n: 1 }
```

Fast retype (`ru` then `run`) uses a stale guard so slow responses do not win. After 150ms, `trail` resolves with one result.

### Full search module

Import from `search.js` for the complete view model:

```javascript
import { debounce } from './debounce.js';
import { executeSearch, render } from './search.js';

const onQuery = debounce(async (query) => {
  const view = await executeSearch(query);
  console.log(render(view));
}, 200);
```

Wire `onQuery` to an `input` handler in a page to port the console module to a real search box.

### Manual checks

1. `executeSearch('')` → empty state, not error.
2. `executeSearch('fail')` → error state with `503`.
3. `executeSearch('run')` then a newer query → stale response ignored.
