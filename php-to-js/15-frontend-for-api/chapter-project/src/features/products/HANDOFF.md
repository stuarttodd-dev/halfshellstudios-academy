# Products admin SPA

App URL: https://app.example  
API URL: https://api.example

## Smoke

1. Login (admin-token against chapter 14 API)
2. `/products?q=widget`
3. Create → 422 → fix → 201
4. Edit and delete from `/products/:id/edit`

## Local dev

```bash
# Terminal 1 — chapter 14 API
cd ../14-build-rest-api/chapter-project && npm start

# Terminal 2 — this SPA
cp .env.example .env
npm run dev
```

## Rollback

- web deploy: `def456`
- api deploy: `abc123`
