import { apiFetch } from './client.js';

export const productsApi = {
  list({ page = 1, q = '' } = {}) {
    const params = new URLSearchParams({ page: String(page) });
    if (q) params.set('q', q);
    return apiFetch(`/api/products?${params}`);
  },
  get(id) {
    return apiFetch(`/api/products/${id}`);
  },
  create(body) {
    return apiFetch('/api/products', { method: 'POST', body });
  },
  update(id, body) {
    return apiFetch(`/api/products/${id}`, { method: 'PATCH', body });
  },
  remove(id) {
    return apiFetch(`/api/products/${id}`, { method: 'DELETE' });
  },
};
