# Chapter 11 — Framework Thinking

Component factories, one-way flow, derived UI, and a single app coordinator — the blueprint you will recognise in Vue.

## Chapter project

| File | What it shows |
|------|---------------|
| `chapter-project/visible-items.js` | Pure derived list helper |
| `chapter-project/create-counter.js` | `createCounter` — increment/decrement, `render()`, `destroy()` |
| `chapter-project/create-tabs.js` | `createTabs` — active tab marker |
| `chapter-project/create-filterable-list.js` | `createFilterableList` — derived rows |
| `chapter-project/create-app.js` | `createApp` coordinator + `dispatch` |
| `chapter-project/main.js` | Lesson sandbox demo |
| `chapter-project/run-components.js` | Same flow via component factories |

### How to run — lesson sandbox

From the **repository root**:

```bash
node php-to-js/11-framework-thinking/chapter-project/main.js
```

Expected output:

```
tab:overview filter:all count:0
 - Widget
 - Gadget
tab:details filter:all count:0
 - Widget
 - Gadget
tab:details filter:active count:0
 - Widget
tab:details filter:active count:1
 - Widget
tab:details filter:active count:2
 - Widget
```

Tab `details`, filter `active` (one item), count `2`.

### How to run — component system

```bash
node php-to-js/11-framework-thinking/chapter-project/run-components.js
```

Same end state, with an extra `ui:` line showing tabs, counter, and list components.

Requires **Node.js v18+**. No `npm install`.

### What maps to Vue later

| This project | Vue equivalent |
|--------------|----------------|
| `create-app.js` | Root app + Pinia/store |
| `create-tabs.js` | Tab SFC with props + emit |
| `create-filterable-list.js` | List SFC with computed |
| `create-counter.js` | Counter SFC with emit |
| `visible-items.js` | `computed()` |
