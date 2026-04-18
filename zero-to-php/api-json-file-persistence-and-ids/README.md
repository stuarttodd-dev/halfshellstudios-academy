# Chapter 16.22 — API persistence + IDs

Basic solution for `api-json-file-persistence-and-ids`.

```php
<?php
declare(strict_types=1);

function loadItems(string $path): array {
    if (!is_file($path)) {
        return [];
    }
    $json = file_get_contents($path);
    return $json ? json_decode($json, true, 512, JSON_THROW_ON_ERROR) : [];
}

function nextId(array $items): int {
    return $items === [] ? 1 : (max(array_column($items, 'id')) + 1);
}
```

Persist to `storage/items.json` after each create so IDs and data survive restarts.

## Solution walkthrough

`loadItems()` handles missing/empty storage safely and decodes valid JSON into arrays.  
`nextId()` generates an incremental id from current records to keep IDs stable.

## How to test

1. From this folder, start the API:
   ```bash
   php -S 127.0.0.1:8030 -t public
   ```
2. Create items and list them:
   ```bash
   curl -i -X POST http://127.0.0.1:8030 -H "Content-Type: application/json" -d '{"name":"Tee","price":1999}'
   curl -i -X POST http://127.0.0.1:8030 -H "Content-Type: application/json" -d '{"name":"Mug","price":1299}'
   curl -i http://127.0.0.1:8030
   ```
3. Restart the server and re-run `curl -i http://127.0.0.1:8030` to confirm persisted items and stable incremental ids.

← [Zero to PHP](../README.md)
