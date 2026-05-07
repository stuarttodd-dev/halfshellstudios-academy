# Chapter 7 — Working with APIs

This chapter covers building a production-shaped HTTP client in vanilla JS — comparable to Guzzle or Laravel's `Http` facade — with typed errors, retries, and parallel requests.

## Files

| Path | What it shows |
|---|---|
| `chapter-project/index.js` | `HttpClient` class with GET/POST, custom `ApiError`, exponential-backoff retry |
| `snippets/fetch-patterns.js` | Tri-state loading pattern (idle/loading/success/error) — the model behind SWR/React Query |

## Run the chapter project

```bash
node chapter-project/index.js
```

Requires Node.js v18+ (built-in `fetch`). Makes real requests to PokéAPI and httpbin.org.
