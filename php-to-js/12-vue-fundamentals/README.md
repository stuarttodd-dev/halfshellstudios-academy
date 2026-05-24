# Chapter 12 — Vue Fundamentals

A product admin feature module: list with search, loading/error/empty states, and a create form — composable + API layer, no `fetch` in presentational children.

## Chapter project structure

```
chapter-project/src/features/products/
  ProductAdminPage.vue
  components/
    ProductRow.vue
    ProductCreateForm.vue
  composables/
    useProducts.js
  api/
    products.js
```

## PHP equivalents

| File | Laravel analogue |
|------|------------------|
| `api/products.js` | API resource / HTTP client |
| `composables/useProducts.js` | Controller + view model |
| `ProductAdminPage.vue` | Blade view + layout |
| `ProductCreateForm.vue` | Form request partial |
| `ProductRow.vue` | List row partial |

### How to run — console demo (lesson sandbox)

From the **repository root**:

```bash
node php-to-js/12-vue-fundamentals/chapter-project/main.js
```

Expected output:

```
load Widget
load Widget
created Gadget
load Widget, Gadget
```

### How to run — Vue app

```bash
cd php-to-js/12-vue-fundamentals/chapter-project
npm install
npm run dev
```

Open the URL Vite prints (usually `http://localhost:5173`). The in-memory API mocks `GET /api/products` and `POST /api/products` behaviour locally.

```bash
npm run build
```

Wire routes and `@vite` in Laravel when you integrate with a real backend.
