# Chapter 16.23 — API errors/status/smoke tests

Basic solution for `api-error-shape-status-codes-and-smoke-tests`.

Use one consistent error shape:

```json
{
  "error": {
    "code": "validation_failed",
    "message": "name is required"
  }
}
```

Status code rules:
- `200` successful reads
- `201` successful creates
- `400` validation/input problems
- `404` unknown route or missing resource
- `500` unexpected server errors

Smoke test with `curl -i` for `/health`, list, create valid, create invalid, and unknown route.

## Solution walkthrough

This standardizes one error JSON shape (`error.code`, `error.message`) across all failure paths.  
It also defines clear status code rules so API clients can handle responses consistently.

## How to test

1. From this folder, start the API:
   ```bash
   php -S 127.0.0.1:8031 -t public
   ```
2. Run a 5-command smoke suite:
   ```bash
   curl -i http://127.0.0.1:8031/health
   curl -i http://127.0.0.1:8031/api/items
   curl -i -X POST http://127.0.0.1:8031/api/items -H "Content-Type: application/json" -d '{"name":"Tee","price":1999}'
   curl -i -X POST http://127.0.0.1:8031/api/items -H "Content-Type: application/json" -d '{"name":"","price":0}'
   curl -i http://127.0.0.1:8031/nope
   ```
3. Confirm status codes match the lesson and all error responses share `error.code` and `error.message`.

← [Zero to PHP](../README.md)
