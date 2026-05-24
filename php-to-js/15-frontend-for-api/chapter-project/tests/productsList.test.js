import { afterEach, describe, expect, it, vi } from 'vitest';
import { productsApi } from '../src/api/products.js';

describe('productsApi.list mock', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('returns paginated data from the API contract', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn(async () => ({
        ok: true,
        status: 200,
        text: async () =>
          JSON.stringify({
            data: [{ id: 1, name: 'Widget', price: 10, sku: null }],
            meta: { page: 1, per_page: 15, total: 1 },
          }),
      })),
    );

    const response = await productsApi.list({ page: 1, q: 'widget' });
    expect(response.data).toHaveLength(1);
    expect(response.data[0].name).toBe('Widget');
  });
});
