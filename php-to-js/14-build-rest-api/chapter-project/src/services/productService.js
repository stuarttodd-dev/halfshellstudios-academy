const products = new Map();
let nextId = 1;

[
  { name: 'Widget', price: 19.99, sku: 'WDG-001' },
  { name: 'Gadget', price: 9.5, sku: 'GDG-002' },
].forEach((seed) => {
  const id = nextId++;
  products.set(id, { id, ...seed });
});

export function listProducts({ page = 1, perPage = 15, q = '' } = {}) {
  const query = q.trim().toLowerCase();
  const filtered = [...products.values()].filter((product) =>
    query ? product.name.toLowerCase().includes(query) : true,
  );
  const total = filtered.length;
  const start = (page - 1) * perPage;
  const data = filtered.slice(start, start + perPage);
  return { data, meta: { page, per_page: perPage, total } };
}

export function findProduct(id) {
  return products.get(Number(id)) ?? null;
}

export function createProduct(payload) {
  const id = nextId++;
  const product = {
    id,
    name: payload.name.trim(),
    price: Number(payload.price),
    sku: payload.sku?.trim() || null,
  };
  products.set(id, product);
  return product;
}

export function updateProduct(id, payload) {
  const existing = findProduct(id);
  if (!existing) return null;
  const updated = {
    ...existing,
    ...(payload.name !== undefined && { name: payload.name.trim() }),
    ...(payload.price !== undefined && { price: Number(payload.price) }),
    ...(payload.sku !== undefined && { sku: payload.sku?.trim() || null }),
  };
  products.set(Number(id), updated);
  return updated;
}

export function deleteProduct(id) {
  return products.delete(Number(id));
}

export function resetProducts(seed = []) {
  products.clear();
  nextId = 1;
  seed.forEach((item) => {
    const id = nextId++;
    products.set(id, { id, ...item });
  });
}
