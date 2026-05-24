export function validateStoreProduct(body = {}) {
  const errors = {};
  const name = String(body.name ?? '').trim();
  const price = Number(body.price);

  if (!name) errors.name = ['The name field is required.'];
  if (!Number.isFinite(price) || price < 0) errors.price = ['The price must be a positive number.'];

  return errors;
}

export function validateUpdateProduct(body = {}) {
  const errors = {};
  if (body.name !== undefined && !String(body.name).trim()) {
    errors.name = ['The name field is required.'];
  }
  if (body.price !== undefined) {
    const price = Number(body.price);
    if (!Number.isFinite(price) || price < 0) {
      errors.price = ['The price must be a positive number.'];
    }
  }
  return errors;
}
