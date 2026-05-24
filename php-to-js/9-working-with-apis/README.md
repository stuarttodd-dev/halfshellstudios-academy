# Chapter 9 — Working with APIs

A small CRUD client module — list, read, create, update, delete — with shared `apiFetch` error handling.

## Chapter project

| File | What it shows |
|------|---------------|
| `chapter-project/todos-api.js` | `apiFetch` + `todosApi` (JSONPlaceholder demo) |
| `chapter-project/main.js` | Runs list → get → create → update → delete |

### How to run

From the **repository root**:

```bash
node php-to-js/9-working-with-apis/chapter-project/main.js
```

Or `cd` into this chapter first:

```bash
cd php-to-js/9-working-with-apis
node chapter-project/main.js
```

Requires **Node.js v18+** and network access (calls `jsonplaceholder.typicode.com`). No `npm install`.

Expected output:

```
list 3
get 1 delectus aut autem
create 201
update true
delete ok
```

Five labelled steps — list count, single get, create id, patched field, delete success. Errors are normalised in one place: `apiFetch`.

Port `BASE` to your Laravel `/api/v1/...` routes when ready; keep the same method names on `todosApi`.
