let products = [{ id: 1, name: 'Widget', price: 10 }];
let simulateServerError = false;

function delay(ms = 80) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

export function setSimulateServerError(value) {
  simulateServerError = value;
}

export async function fetchProducts(query = '') {
  await delay();
  if (simulateServerError) {
    const error = new Error('Internal server error');
    error.status = 500;
    throw error;
  }
  const q = query.toLowerCase();
  return {
    data: products.filter((product) => product.name.toLowerCase().includes(q)),
  };
}

export async function createProduct({ name, price }) {
  await delay();
  if (simulateServerError) {
    const error = new Error('Internal server error');
    error.status = 500;
    throw error;
  }

  const trimmed = String(name ?? '').trim();
  const amount = Number(price);

  if (!trimmed) {
    const error = new Error('Validation failed');
    error.status = 422;
    error.errors = { name: ['Name is required.'] };
    throw error;
  }

  if (!Number.isFinite(amount) || amount < 0) {
    const error = new Error('Validation failed');
    error.status = 422;
    error.errors = { price: ['Price must be a positive number.'] };
    throw error;
  }

  const product = { id: Date.now(), name: trimmed, price: amount };
  products = [...products, product];
  return { data: product };
}

export function resetProducts(seed = [{ id: 1, name: 'Widget', price: 10 }]) {
  products = [...seed];
  simulateServerError = false;
}
