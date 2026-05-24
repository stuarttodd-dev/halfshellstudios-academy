import { formatCurrency, truncate, slugify } from './formatter.js';

const products = [
  { title: 'Running shoes — Trail Edition', price: 89.99 },
  { title: 'Merino Wool Socks', price: 12.5 },
];

for (const { title, price } of products) {
  const label = truncate(title, 24);
  let amount;
  try {
    amount = formatCurrency(price);
  } catch {
    amount = '—';
  }
  const slug = slugify(title);
  console.log(`${label.padEnd(28)} ${amount.padStart(8)}  /products/${slug}`);
}
