# Chapter 3 — Data Handling: Arrays, Strings & Objects

Parsing, normalisation, and list methods — transform a messy API payload into rows ready for a table component.

## Chapter project

Take raw API data, normalise each order and line item, filter invalid rows, and derive display fields for the UI.

| File | What it shows |
|------|---------------|
| `chapter-project/transform.js` | `normaliseLine`, `normaliseOrder`, `toTableRows` |
| `chapter-project/main.js` | Sample API payload and console output |

### How to run the chapter project

From the **repository root**:

```bash
node php-to-js/3-data-handling-arrays-strings-objects/chapter-project/main.js
```

Or `cd` into this chapter first:

```bash
cd php-to-js/3-data-handling-arrays-strings-objects
node chapter-project/main.js
```

Requires **Node.js v18+**. No `npm install` — the project uses plain ESM (`package.json` only sets `"type": "module"`).

Expected output:

```
[
  {
    id: 10,
    statusLabel: 'PAID',
    lineCount: 1,
    totalDisplay: '£42.50'
  }
]
```

One table row for order `10` with `lineCount` 1 and a formatted total. Order `id: 0` is filtered out.
