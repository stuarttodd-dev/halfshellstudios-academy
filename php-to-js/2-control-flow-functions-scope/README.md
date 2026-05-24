# Chapter 2 — Control Flow, Functions & Scope

Guard clauses, pure formatters, `map` / `filter` / `reduce`, and error-first parsing — composed into a small receipt pipeline.

## Chapter project

Build a formatter pipeline that takes raw line items and returns display-ready rows plus a total.

| File | What it shows |
|------|---------------|
| `chapter-project/receipt.js` | `parseMoney`, `formatCurrency`, `formatLine`, `buildReceipt` |
| `chapter-project/main.js` | Parse raw strings, skip invalid rows, log the receipt |

### How to run the chapter project

From the **repository root**:

```bash
node php-to-js/2-control-flow-functions-scope/chapter-project/main.js
```

Or `cd` into this chapter first:

```bash
cd php-to-js/2-control-flow-functions-scope
node chapter-project/main.js
```

Requires **Node.js v18+**. No `npm install` — the project uses plain ESM (`package.json` only sets `"type": "module"`).

Expected output:

```
{
  rows: [
    { display: 'Plan: £19.99' },
    { display: 'Trial: £0.00' }
  ],
  total: '£19.99'
}
skip Bad Amount must be a number
```

Two billable rows (draft `Note` is excluded), a formatted total, and a logged skip for the invalid amount.
