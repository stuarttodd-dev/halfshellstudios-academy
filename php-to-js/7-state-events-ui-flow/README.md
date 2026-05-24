# Chapter 7 — State, Events & UI Flow

Single source of truth, derived lists, thin handlers, and separated state vs render — built as a stateful task board.

## Chapter project

| File | What it shows |
|------|---------------|
| `chapter-project/store.js` | `visibleTasks`, `openCount`, `addTask`, `toggleTask`, `setFilter` |
| `chapter-project/render.js` | DOM-only render with `textContent` / `replaceChildren` |
| `chapter-project/page.js` | Event → state → render wiring + URL filter sync |
| `chapter-project/index.html` | Filter tabs, add form, task list, open count badge |
| `chapter-project/main.js` | Console demo (lesson sandbox) |

### How to run — console demo

From the **repository root**:

```bash
node php-to-js/7-state-events-ui-flow/chapter-project/main.js
```

Expected output:

```
open titles [ 'B' ]
openCount 1
```

### How to run — browser task board

Open `chapter-project/index.html` in your browser, or serve locally:

```bash
cd php-to-js/7-state-events-ui-flow/chapter-project
npx --yes serve .
```

Then open the URL shown (usually `http://localhost:3000`).

### What to try in the browser

1. Add a task — list and open count update without reload.
2. Toggle done — row styling changes; filter tabs `open` / `done` show the right subset.
3. Switch filter tabs — `?filter=open` syncs to the URL; refresh preserves the active filter.

No business rules live inside `render`. User titles use `textContent`, not `innerHTML`.
