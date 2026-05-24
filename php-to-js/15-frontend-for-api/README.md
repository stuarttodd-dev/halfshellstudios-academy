# Chapter 15 — Frontend for API

Vue SPA that consumes chapter 14's Products API v1 — list, search, create, edit, delete, with auth, tests, and handoff docs.

## Chapter project

| Piece | Location |
|-------|----------|
| API client | `src/api/client.js` |
| Products CRUD | `src/api/products.js` |
| Feature module | `src/features/products/` |
| Router | `/products`, `/products/new`, `/products/:id/edit` |
| Auth | `src/auth.js`, `src/views/LoginPage.vue` |
| Tests | `tests/` (422, list mock, guard) |
| Handoff | `src/features/products/HANDOFF.md` |

### How to run — handoff checklist

```bash
node php-to-js/15-frontend-for-api/chapter-project/main.js
```

Expected output:

```
PASS api client
PASS router + auth
PASS crud screens
TODO vitest tests
TODO HANDOFF.md
project incomplete
```

(Tests and `HANDOFF.md` exist in the repo; the sandbox marks them TODO until your team completes staging smoke.)

### How to run — dev with chapter 14 API

```bash
# Terminal 1
cd php-to-js/14-build-rest-api/chapter-project && npm start

# Terminal 2
cd php-to-js/15-frontend-for-api/chapter-project
cp .env.example .env
npm install
npm start
```

The dev server proxies `/api` to `http://localhost:3000`, so you avoid CORS errors locally. Chapter 14 also sends CORS headers for `http://localhost:*` if you set `VITE_API_BASE_URL=http://localhost:3000` instead.

Sign in with **admin-token** (same token chapter 14 accepts).

### CORS and the dev proxy

The Vue app (e.g. `http://localhost:5174`) and the API (`http://localhost:3000`) are **different origins**. Browsers block cross-origin `fetch` calls unless the API responds with CORS headers — you may see:

```
Access to fetch at 'http://localhost:3000/api/...' from origin 'http://localhost:5174'
has been blocked by CORS policy
```

**Default fix (recommended for local dev):** leave `VITE_API_BASE_URL` empty in `.env`. Requests go to relative paths like `/api/products`, and Vite proxies them to the chapter 14 server (`vite.config.js` → `server.proxy['/api']`).

**Alternative:** set `VITE_API_BASE_URL=http://localhost:3000` in `.env`. Chapter 14 enables CORS for any `http://localhost:*` origin so direct browser calls work.

If you still see CORS errors after changing `.env`:

1. Restart **both** terminals (`npm start` in chapter 14 and chapter 15).
2. Confirm `.env` matches one of the setups above — not a stale mix of both.
3. Confirm the API is running on port 3000 (`curl http://localhost:3000/health`).

For production/staging builds, set `VITE_API_BASE_URL` to the real API host before `npm run build`.

### Tests and build

```bash
npm test
npm run build
```

Set `VITE_API_BASE_URL` in `.env` for staging builds.

## Links to prior chapters

| Chapter | Used here |
|---------|-----------|
| 12 | SFCs, composables, forms |
| 13 | Guards, submission states, field errors |
| 14 | API contract, pagination, Bearer auth |
