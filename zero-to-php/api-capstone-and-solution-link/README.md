# Chapter 16.24 — API capstone

Basic capstone checklist for `api-capstone-and-solution-link`.

- `GET /health` works with JSON output.
- `GET /api/items` and `POST /api/items` follow one contract.
- Validation failures use a stable error shape.
- Item writes persist to `storage/items.json`.

Reference: [Zero to PHP - Basic API build](https://github.com/stuartp-dev/zero-to-php-basic-api-build)

## Solution walkthrough

This capstone combines API routing, list/create contracts, validation, error shape, and file persistence into one beginner API slice.  
It reflects a complete minimal workflow that clients can reliably consume.

## How to test

1. From this folder, start the API:
   ```bash
   php -S 127.0.0.1:8024 -t public
   ```
2. Run smoke tests:
   ```bash
   curl -i http://127.0.0.1:8024/health
   curl -i http://127.0.0.1:8024/api/items
   curl -i -X POST http://127.0.0.1:8024/api/items -H "Content-Type: application/json" -d '{"name":"Tee","price":1999}'
   curl -i -X POST http://127.0.0.1:8024/api/items -H "Content-Type: application/json" -d '{"name":"","price":0}'
   curl -i http://127.0.0.1:8024/nope
   ```
3. Confirm status codes and error JSON shape are consistent.
4. Stop/restart the server and confirm created items persist in `storage/items.json`.

← [Zero to PHP](../README.md)
