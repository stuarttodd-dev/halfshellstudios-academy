import { Cart, LineItem } from './cart.js';

const cart = Cart.fromJSON({
  lines: [
    { sku: 'A1', qty: 2, unit_price: 10 },
    { sku: 'B2', qty: 1, unit_price: 5 },
  ],
});

const first = cart.lines[0];
const bumped = first.withQty(3);

console.log('subtotal', cart.subtotal(), 'old qty', first.qty, 'new qty', bumped.qty);
console.log('wire', JSON.stringify(cart));

try {
  LineItem.fromJSON({ sku: '  ', qty: 1, unit_price: 1 });
} catch (e) {
  console.log('validation', e.message);
}
