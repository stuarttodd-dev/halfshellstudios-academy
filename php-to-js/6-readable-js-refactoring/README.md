# Chapter 6 — Readable JS & Refactoring

Split a legacy checkout monolith into feature-shaped modules: validate, build payload, submit, render, and a thin binder — same behaviour, clearer boundaries.

## Chapter project

| File | What it shows |
|------|---------------|
| `chapter-project/legacy-checkout.js` | Original monolith (reference only — do not import) |
| `chapter-project/features/checkout/validate-checkout.js` | Guard-clause validation, no I/O |
| `chapter-project/features/checkout/build-order-payload.js` | `PENCE_MULTIPLIER`, payload shape |
| `chapter-project/features/checkout/submit-order.js` | HTTP only — no DOM |
| `chapter-project/features/checkout/render-checkout.js` | Success/error UI state |
| `chapter-project/features/checkout/bind-checkout.js` | Wires modules; page imports this |
| `chapter-project/main.js` | Console demo with mocked `http` |

### How to run the chapter project

From the **repository root**:

```bash
node php-to-js/6-readable-js-refactoring/chapter-project/main.js
```

Or `cd` into this chapter first:

```bash
cd php-to-js/6-readable-js-refactoring
node chapter-project/main.js
```

Requires **Node.js v18+**. No `npm install` — the project uses plain ESM (`package.json` only sets `"type": "module"`).

Expected output:

```
{ ok: true, orderId: 'ord_1', payload: { name: 'Ada', amount: 1250 } }
```

Validation lives in `validate-checkout.js` (no `fetch`). HTTP lives in `submit-order.js` (no DOM). The page would import `bindCheckout,` which orchestrates both.

### Manual checks

1. Valid `{ name: 'Ada', amount: '12.50' }` → `ok: true`, `amount: 1250` in payload.
2. `{ name: 'A', amount: '12.50' }` → `Name too short.`
3. `{ name: 'Ada', amount: '0' }` → `Invalid amount.`
