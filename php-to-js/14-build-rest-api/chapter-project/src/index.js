import express from 'express';
import { requireAuth } from './middleware/auth.js';
import productsRouter from './routes/products.js';

const ME_PROFILES = {
  admin: { id: 1, role: 'admin', email: 'admin@example.com' },
  member: { id: 2, role: 'member', email: 'member@example.com' },
};

export function createApp() {
  const app = express();
  app.use(express.json());

  app.use((req, res, next) => {
    const origin = req.headers.origin;
    if (origin && /^http:\/\/localhost:\d+$/.test(origin)) {
      res.setHeader('Access-Control-Allow-Origin', origin);
      res.setHeader('Access-Control-Allow-Credentials', 'true');
      res.setHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
      res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept');
    }
    if (req.method === 'OPTIONS') {
      return res.sendStatus(204);
    }
    next();
  });

  app.get('/health', (_req, res) => {
    res.json({ status: 'ok' });
  });

  app.get('/api/me', requireAuth, (req, res) => {
    res.json({ data: ME_PROFILES[req.user.role] });
  });

  app.use('/api/products', requireAuth, productsRouter);

  return app;
}

const app = createApp();
const PORT = process.env.PORT ?? 3000;

if (process.env.NODE_ENV !== 'test') {
  app.listen(PORT, () => {
    console.log(`Products API v1 at http://localhost:${PORT}/api/products`);
    console.log('Tokens: admin-token (mutate), member-token (read only)');
  });
}

export default app;
