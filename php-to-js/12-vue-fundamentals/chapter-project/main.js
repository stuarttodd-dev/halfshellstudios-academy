import { createProduct, fetchProducts, resetProducts } from './src/features/products/api/products.js';

resetProducts();

let search = '';

async function load() {
  const { data } = await fetchProducts(search);
  console.log('load', data.map((product) => product.name).join(', ') || '(empty)');
  return data;
}

async function create(payload) {
  await createProduct(payload);
  console.log('created', payload.name);
}

await load();
search = 'wid';
await load();
await create({ name: 'Gadget', price: 5 });
search = '';
await load();
