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

1. Run a 5-command smoke suite:
   - `GET /health`
   - `GET /api/items`
   - `POST /api/items` valid
   - `POST /api/items` invalid
   - unknown route
2. Confirm status codes match this README and all errors use the same JSON structure.

← [Zero to PHP](../README.md)
