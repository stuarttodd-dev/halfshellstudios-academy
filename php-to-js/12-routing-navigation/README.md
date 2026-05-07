# Chapter 12 — Routing and Navigation

Client-side routing from scratch: URL pattern matching, named params, history API, and how Vue Router builds on these same ideas.

## Run the chapter project

```bash
node chapter-project/router.js
```

No dependencies required. (The full history API integration requires a browser; the demo simulates navigation calls.)

## Files

| File | What it shows |
|------|---------------|
| `chapter-project/router.js` | ~60-line client-side router: regex pattern matching, named params, onChange listeners |

## Key ideas for PHP developers

| Laravel | Client-side Router |
|---------|--------------------|
| `Route::get('/users/{id}', ...)` | `.add('/users/:id', component)` |
| `route('users.show', $id)` | `router.navigate('/users/42')` |
| Middleware | `router.onChange` hook / navigation guards |
| `redirect()->route(...)` | `router.navigate('/other')` |

Named params work the same way — `:name` in the path becomes `params.name` in the handler, just like `{name}` in Laravel route definitions.
