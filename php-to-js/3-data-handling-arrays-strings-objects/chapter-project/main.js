import { toTableRows } from './transform.js';

const sample = {
  data: [
    { id: '10', status: 'paid', total: '42.5', lines: [{ sku: 'A1', quantity: 1 }] },
    { id: 0, status: 'draft', lines: [] },
  ],
};

console.log(toTableRows(sample));
