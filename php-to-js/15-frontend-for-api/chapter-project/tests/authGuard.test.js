import { describe, expect, it } from 'vitest';
import { authGuard, user } from '../src/auth.js';

describe('authGuard', () => {
  it('redirects guests to login with redirect query', () => {
    user.value = null;
    const result = authGuard({ meta: { requiresAuth: true }, fullPath: '/products?q=widget' });
    expect(result).toEqual({ path: '/login', query: { redirect: '/products?q=widget' } });
  });

  it('allows authenticated users through', () => {
    user.value = { id: 1, role: 'admin' };
    expect(authGuard({ meta: { requiresAuth: true }, fullPath: '/products' })).toBe(true);
  });
});
