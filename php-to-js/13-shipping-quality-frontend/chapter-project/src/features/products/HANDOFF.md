# Product admin feature

## Staging URL

/products

## Verify

1. List loads
2. Search filters results
3. Create valid product → appears in list
4. Create invalid → field errors from 422
5. Simulate API 500 → banner error and error report

## Flags

`VITE_FEATURE_PRODUCT_ADMIN` (default off in prod)

## Rollback

Disable flag or revert deploy tag `abc123`

## Local commands

```bash
npm install
npm run dev
npm test
npm run build
```

## Manual 500 test

In the browser console on `/products`:

```javascript
import { setSimulateServerError } from './src/features/products/api/products.js';
setSimulateServerError(true);
```

Then click Reload on the list page and inspect the console for a `report` payload.
