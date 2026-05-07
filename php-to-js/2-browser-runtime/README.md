# Chapter 2 — Browser Runtime

This chapter covers the browser as a JavaScript runtime: the DOM, events, the fetch API, and how client-side JS interacts with a page. PHP developers will notice the shift from server-side rendering to live DOM manipulation.

## Chapter project

A self-contained contact form with client-side validation. No build step, no frameworks — just HTML and inline JS.

Open `chapter-project/index.html` directly in your browser (File → Open, or drag-and-drop). No server required.

## Snippets

These files are written as if running in a browser context (they need a DOM). Paste them into your browser's DevTools console, or drop them into a `<script>` tag.

| File | What it demonstrates |
|------|----------------------|
| `snippets/event-delegation.js` | One parent listener handling many children via bubbling |
| `snippets/fetch-dom.js` | `fetch()` + async/await + DOM update pattern |
