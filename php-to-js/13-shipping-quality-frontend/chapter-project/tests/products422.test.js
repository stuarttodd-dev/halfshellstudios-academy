import { describe, expect, it } from 'vitest';
import { createProduct, resetProducts } from '../src/features/products/api/products.js';

describe('createProduct 422 integration', () => {
  it('maps validation errors like Laravel 422', async () => {
    resetProducts();

    await expect(createProduct({ name: '', price: 10 })).rejects.toMatchObject({
      status: 422,
      errors: { name: ['Name is required.'] },
    });
  });
});
