# Chapter 4 — Browser Runtime, DOM & Forms

Selectors, events, `preventDefault`, validation, safe DOM updates, state-driven rendering, event delegation, and optional `localStorage` — wired into an interactive form and result list.

## Chapter project

Submit a form, validate on `input` and `submit`, push rows into an in-memory array, re-render the list with `replaceChildren`, and persist drafts and rows in `localStorage`.

| File | What it shows |
|------|---------------|
| `chapter-project/index.html` | Form markup with `data-role` hooks and list container |
| `chapter-project/style.css` | `.is-error` / `.is-success` field states |
| `chapter-project/app.js` | `validateLabel`, `renderList`, storage helpers |
| `chapter-project/main.js` | Submit handler, inline validation, delegation, init |

### How to run the chapter project

This is a **browser** project — open the HTML file in your browser:

1. Open `chapter-project/index.html` (File → Open, or drag into a browser tab).

Or serve the folder locally (recommended for ES modules):

```bash
cd php-to-js/4-browser-runtime-dom-forms/chapter-project
npx --yes serve .
```

Then open the URL shown (usually `http://localhost:3000`).

No `npm install` required unless you use `npx serve`.

### What to try

1. Submit with an empty label — inline error appears (`textContent`, not `innerHTML`).
2. Add **Buy milk** and **Eggs** — two rows appear in the list.
3. Refresh the page — rows (and any draft text) are still there via `localStorage`.
4. Click a row — event delegation removes it from state and re-renders.

### Console sketch (lesson sandbox)

The lesson sandbox simulates validate → push → render without a page. The same `validateLabel` and `renderList` functions live in `app.js`; the browser project wires them to real DOM nodes.
