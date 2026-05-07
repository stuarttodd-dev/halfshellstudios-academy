// Utility library — pure functions that mirror common PHP/Laravel helpers.
// No dependencies; run with: node chapter-project/index.js

// ---------------------------------------------------------------------------
// Formatting
// ---------------------------------------------------------------------------

// Intl.NumberFormat handles locale-aware currency — no external library needed.
// PHP equivalent: number_format() + manual currency symbol, or Money library.
const formatCurrency = (pence, currency = 'GBP') =>
  new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(pence / 100);

// PHP equivalent: (new DateTime($iso))->format('d M Y')
const formatDate = (iso) =>
  new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' }).format(new Date(iso));

// ---------------------------------------------------------------------------
// Object / array helpers
// ---------------------------------------------------------------------------

// pick: select a subset of keys from an object.
// PHP equivalent: Arr::only($array, ['id', 'total'])  or  $request->only(...)
const pick = (obj, keys) =>
  Object.fromEntries(keys.filter(k => k in obj).map(k => [k, obj[k]]));

// groupBy: group an array of objects by a key or key-selector function.
// PHP equivalent: Collection::groupBy('status')
const groupBy = (arr, key) =>
  arr.reduce((acc, item) => {
    const k = typeof key === 'function' ? key(item) : item[key];
    (acc[k] ??= []).push(item);
    return acc;
  }, {});

// ---------------------------------------------------------------------------
// Functional composition
// ---------------------------------------------------------------------------

// compose: right-to-left (mathematical order: f(g(x)))
// The last function in the list is applied first.
const compose = (...fns) => (x) => fns.reduceRight((v, f) => f(v), x);

// pipe: left-to-right (more readable for most developers)
// PHP equivalent: chaining Collection methods or using |> (pipeline operator proposal)
const pipe = (...fns) => (x) => fns.reduce((v, f) => f(v), x);

// ---------------------------------------------------------------------------
// Demo
// ---------------------------------------------------------------------------

const orders = [
  { id: 1, status: 'paid',    total: 1999, date: '2024-03-15' },
  { id: 2, status: 'pending', total:  999, date: '2024-03-16' },
  { id: 3, status: 'paid',    total: 4999, date: '2024-03-16' },
  { id: 4, status: 'refund',  total: 1999, date: '2024-03-17' },
];

console.log('formatCurrency(1999):', formatCurrency(1999));        // £19.99
console.log('formatDate:', formatDate('2024-03-15'));              // 15 Mar 2024
console.log('pick:', pick(orders[0], ['id', 'total']));           // { id: 1, total: 1999 }
console.log('groupBy status keys:', Object.keys(groupBy(orders, 'status'))); // ['paid','pending','refund']

// pipe chains transformations left-to-right — each step receives the output
// of the previous step. This replaces deeply nested function calls.
const summarisePaidOrders = pipe(
  (orders) => orders.filter(o => o.status === 'paid'),
  (orders) => orders.map(o => o.total),
  (totals) => totals.reduce((sum, t) => sum + t, 0),
  (total)  => formatCurrency(total),
);

console.log('Paid total:', summarisePaidOrders(orders)); // £69.98

// compose runs right-to-left — useful when thinking mathematically.
const getMaxTotal = compose(
  (n) => formatCurrency(n),
  (totals) => Math.max(...totals),
  (orders) => orders.map(o => o.total),
);

console.log('Max order:', getMaxTotal(orders)); // £49.99
