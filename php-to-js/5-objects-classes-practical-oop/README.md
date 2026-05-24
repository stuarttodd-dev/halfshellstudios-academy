# Chapter 5 — Objects, Classes & Practical OOP

A small cart domain model: `LineItem` and `Cart` classes with `fromJSON` validation, `toJSON` for the wire, and immutable-style `withQty`.

## Chapter project

| File | What it shows |
|------|---------------|
| `chapter-project/cart.js` | `LineItem` and `Cart` — totals, serialization, factories |
| `chapter-project/main.js` | Build cart from API-shaped JSON, demo `withQty` and validation |

### How to run the chapter project

From the **repository root**:

```bash
node php-to-js/5-objects-classes-practical-oop/chapter-project/main.js
```

Or `cd` into this chapter first:

```bash
cd php-to-js/5-objects-classes-practical-oop
node chapter-project/main.js
```

Requires **Node.js v18+**. No `npm install` — the project uses plain ESM (`package.json` only sets `"type": "module"`).

Expected output:

```
subtotal 25 old qty 2 new qty 3
wire {"lines":[{"sku":"A1","qty":2,"unit_price":10},{"sku":"B2","qty":1,"unit_price":5}]}
validation sku required
```

Subtotal is `25` (2×10 + 1×5). `withQty(3)` returns a new line without changing the original. Empty `sku` throws like a 422.
