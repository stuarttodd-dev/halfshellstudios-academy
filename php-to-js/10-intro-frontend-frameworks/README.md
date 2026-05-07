# Chapter 10 — Intro to Frontend Frameworks

Understand what frameworks solve by building the same thing without one: a hand-rolled component system and a virtual DOM implementation in plain JS.

## Run the chapter project

```bash
node chapter-project/mini-component.js
```

No dependencies required.

## Files

| File | What it shows |
|------|---------------|
| `chapter-project/mini-component.js` | ~50-line component system: state, subscribers, actions — the pattern Vue/React formalise |
| `snippets/vdom-concept.js` | Virtual DOM as plain objects: `h()` hyperscript helper and a `diff()` function |

## Key ideas

- A component is just state + a render function + event handlers
- Subscriptions replace manual DOM wiring
- Virtual DOM diffs tell you the minimum changes to apply
- Every frontend framework is an opinionated version of these patterns
