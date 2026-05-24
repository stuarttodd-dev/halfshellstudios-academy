import { describe, expect, it } from 'vitest';
import { productSchema, validateSchema } from '../src/features/products/schemas/productSchema.js';

describe('validateSchema', () => {
  it('returns errors for invalid product values', () => {
    const errors = validateSchema(productSchema, { name: '', price: -1 });
    expect(errors.name).toEqual(['Required']);
    expect(errors.price).toEqual(['Must be >= 0']);
  });

  it('returns no errors for valid product values', () => {
    const errors = validateSchema(productSchema, { name: 'Widget', price: 10 });
    expect(errors).toEqual({});
  });
});
