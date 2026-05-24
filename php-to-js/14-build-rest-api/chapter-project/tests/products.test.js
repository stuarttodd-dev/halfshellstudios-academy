import request from 'supertest';
import { beforeEach, describe, expect, it } from 'vitest';
import app from '../src/index.js';
import { resetProducts } from '../src/services/productService.js';
import { tokens } from '../src/middleware/auth.js';

const admin = { Authorization: `Bearer ${tokens.ADMIN_TOKEN}` };
const member = { Authorization: `Bearer ${tokens.MEMBER_TOKEN}` };

beforeEach(() => {
  resetProducts([
    { name: 'Widget', price: 19.99, sku: 'WDG-001' },
    { name: 'Gadget', price: 9.5, sku: 'GDG-002' },
  ]);
});

describe('Products API v1', () => {
  it('index returns 200 for admin with pagination and search', async () => {
    const res = await request(app).get('/api/products?page=1&q=widget').set(admin);
    expect(res.status).toBe(200);
    expect(res.body.data).toHaveLength(1);
    expect(res.body.data[0].name).toBe('Widget');
    expect(res.body.meta.page).toBe(1);
  });

  it('store returns 201 with data.id for admin', async () => {
    const res = await request(app)
      .post('/api/products')
      .set(admin)
      .send({ name: 'Test', price: 1 });
    expect(res.status).toBe(201);
    expect(res.body.data.id).toBeDefined();
    expect(res.body.data.name).toBe('Test');
  });

  it('store returns 422 with errors.name for invalid body', async () => {
    const res = await request(app).post('/api/products').set(admin).send({ name: '', price: -1 });
    expect(res.status).toBe(422);
    expect(res.body.errors.name).toBeDefined();
  });

  it('guest POST returns 401', async () => {
    const res = await request(app).post('/api/products').send({ name: 'Test', price: 1 });
    expect(res.status).toBe(401);
  });

  it('member DELETE returns 403', async () => {
    const res = await request(app).delete('/api/products/1').set(member);
    expect(res.status).toBe(403);
  });

  it('OPTIONS preflight includes CORS headers for localhost dev', async () => {
    const res = await request(app)
      .options('/api/products')
      .set('Origin', 'http://localhost:5174')
      .set('Access-Control-Request-Method', 'GET')
      .set('Access-Control-Request-Headers', 'authorization');

    expect(res.status).toBe(204);
    expect(res.headers['access-control-allow-origin']).toBe('http://localhost:5174');
    expect(res.headers['access-control-allow-credentials']).toBe('true');
  });

  it('GET /api/me returns current user for admin', async () => {
    const res = await request(app).get('/api/me').set(admin);
    expect(res.status).toBe(200);
    expect(res.body.data).toEqual({ id: 1, role: 'admin', email: 'admin@example.com' });
  });

  it('GET /api/me returns 401 without token', async () => {
    const res = await request(app).get('/api/me');
    expect(res.status).toBe(401);
  });
});
