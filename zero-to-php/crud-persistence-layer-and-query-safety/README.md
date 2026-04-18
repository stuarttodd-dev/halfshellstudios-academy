# Chapter 16.17 — CRUD persistence layer

Basic solution for `crud-persistence-layer-and-query-safety`.

```php
<?php
declare(strict_types=1);

final class TaskRepository {
    public function __construct(private PDO $pdo) {}

    public function list(): array {
        return $this->pdo->query('SELECT id, title, status FROM tasks ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $title, string $status): void {
        $stmt = $this->pdo->prepare('INSERT INTO tasks (title, status) VALUES (:title, :status)');
        $stmt->execute(['title' => $title, 'status' => $status]);
    }
}
```

Use prepared statements for all writes/reads that receive input.

## Solution walkthrough

This extracts DB access into `TaskRepository` so handlers stay focused on request/response.  
Prepared statements protect writes from malformed input and keep query behavior predictable.

## How to test

1. Wire repository into list/create routes.
2. Create a record with normal input and confirm it appears in list.
3. Submit edge-case strings (quotes/symbols) and confirm query still works safely.

← [Zero to PHP](../README.md)
