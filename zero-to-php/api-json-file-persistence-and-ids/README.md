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

1. Start with no `storage/items.json`.
2. Create two items through your API create flow and persist each write.
3. Restart server and confirm both records remain and new item gets the next id.

← [Zero to PHP](../README.md)
