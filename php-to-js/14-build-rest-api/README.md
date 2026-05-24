# Chapter 14 — Build a REST API

Products API v1 — CRUD, validation, auth roles, tests, fixtures, and API docs for the chapter 15 Vue client.

## Chapter project

| File | Purpose |
|------|---------|
| `chapter-project/src/index.js` | Express app — `/api/products`, `/api/me`, CORS for localhost |
| `chapter-project/src/routes/products.js` | Index, show, store, update, destroy |
| `chapter-project/src/services/productService.js` | Product persistence (in-memory) |
| `chapter-project/src/validation/productValidation.js` | Store/update validation (422) |
| `chapter-project/src/resources/productResource.js` | Response shape `{ data: { id, name, price, sku } }` |
| `chapter-project/src/middleware/auth.js` | Bearer auth — admin vs member |
| `chapter-project/tests/products.test.js` | Feature tests (CRUD, auth, CORS preflight, `/api/me`) |
| `chapter-project/tests/fixtures/` | Sample JSON for front-end mocks |
| `chapter-project/API.md` | Smoke commands and error shapes |
| `chapter-project/main.js` | Handoff checklist (lesson sandbox) |

### How to run — handoff checklist

```bash
node php-to-js/14-build-rest-api/chapter-project/main.js
```

Expected output:

```
PASS index 200
PASS store 201
PASS store 422
PASS guest 401
PASS forbidden 403
TODO fixtures committed
project incomplete
```

(Fixtures are in the repo; the sandbox marks that step TODO until you wire them in your Laravel project.)

### How to run — API server

```bash
cd php-to-js/14-build-rest-api/chapter-project
npm install
npm start
```

Tokens:

- `admin-token` — full CRUD
- `member-token` — read only (mutate → 403)

```bash
curl -H "Authorization: Bearer admin-token" "http://localhost:3000/api/products?page=1&q=widget"
curl -H "Authorization: Bearer admin-token" http://localhost:3000/api/me
curl -X POST -H "Authorization: Bearer admin-token" -H "Content-Type: application/json" \
  -d '{"name":"Test","price":1}' http://localhost:3000/api/products
```

**CORS:** The API sends `Access-Control-Allow-Origin` for `http://localhost:*` origins so chapter 15 can call it directly from the browser when `VITE_API_BASE_URL=http://localhost:3000`. For local dev, chapter 15 defaults to a Vite proxy instead (see chapter 15 README).

### How to run — tests

```bash
npm test
```

Eight feature tests cover 200, 201, 422, 401, 403, 404, CORS preflight, and `/api/me`.

## Laravel mapping

| This project | Laravel |
|--------------|---------|
| `productValidation.js` | `StoreProductRequest`, `UpdateProductRequest` |
| `productService.js` | `ProductService` |
| `productResource.js` | `ProductResource` |
| `auth.js` | `auth:sanctum` + policy |
| `products.test.js` | `php artisan test` feature tests |
