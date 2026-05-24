# Products API v1

Base: `/api/products`  
Auth: Bearer token (`admin-token` for mutate, `member-token` for read)

## Smoke

```bash
curl -H "Authorization: Bearer admin-token" http://localhost:3000/api/products
curl -X POST -H "Authorization: Bearer admin-token" -H "Content-Type: application/json" \
  -d '{"name":"Test","price":1}' http://localhost:3000/api/products
```

## Endpoints

| Method | Path | Auth | Notes |
|--------|------|------|-------|
| GET | `/api/products?page=1&q=widget` | admin or member | Paginated index |
| GET | `/api/products/:id` | admin or member | Single product |
| POST | `/api/products` | admin | `201` + `data.id` |
| PATCH | `/api/products/:id` | admin | Partial update |
| DELETE | `/api/products/:id` | admin | `204` empty body |

## Error shapes

| Status | When |
|--------|------|
| 401 | Missing/invalid token |
| 403 | Member attempts mutate |
| 404 | Unknown product id |
| 422 | Validation failed — `errors.name` etc. |

## Rollback

Revert migration `2025_xx_xx_create_products_table` if needed (Laravel equivalent).

In this Node demo, restart the process to reset in-memory data.
