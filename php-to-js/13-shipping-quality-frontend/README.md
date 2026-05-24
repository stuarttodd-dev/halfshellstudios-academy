# Chapter 13 — Shipping Quality Frontend

Polished product admin handoff: routes, validation layers, tests, observability, auth guard, and `HANDOFF.md`.

## Chapter project structure

```
chapter-project/src/features/products/
  ProductListPage.vue
  ProductCreatePage.vue
  components/ProductRow.vue
  composables/useProducts.js
  api/products.js
  schemas/productSchema.js
  observability/reportError.js
  HANDOFF.md
```

## Deliverables

| Piece | Location |
|-------|----------|
| Routes `/products`, `/products/new` | `src/router.js` |
| Auth guard + `redirect` query | `src/auth.js`, `src/views/LoginPage.vue` |
| Client schema + 422 mapping | `schemas/productSchema.js`, composable |
| `reportError` on 500 | `observability/reportError.js` |
| Unit + integration tests | `tests/` |
| Handoff doc | `src/features/products/HANDOFF.md` |

### How to run — handoff verification script

```bash
node php-to-js/13-shipping-quality-frontend/chapter-project/main.js
```

Expected output:

```
PASS list loads
PASS search filters
PASS create valid
PASS create 422
BLOCKED api 500 banner
handoff incomplete
```

### How to run — app and tests

```bash
cd php-to-js/13-shipping-quality-frontend/chapter-project
cp .env.example .env
npm install
npm test
npm run dev
```

1. Open the app — sign in on `/login` (mock auth).
2. Visit `/products` and `/products/new`.
3. Create invalid then valid products; confirm field errors and list update.

`npm run build` bundles for production.
