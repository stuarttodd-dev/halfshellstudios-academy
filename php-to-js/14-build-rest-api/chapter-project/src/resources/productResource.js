export function toProductResource(product) {
  return {
    id: product.id,
    name: product.name,
    price: product.price,
    sku: product.sku ?? null,
  };
}

export function toProductCollection(result) {
  return {
    data: result.data.map(toProductResource),
    meta: result.meta,
  };
}
