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

1. Run end-to-end smoke tests for health, list, valid create, invalid create, and unknown route.
2. Confirm JSON content type and status code consistency across all responses.
3. Restart the server and verify persisted items are still returned by list.

← [Zero to PHP](../README.md)
