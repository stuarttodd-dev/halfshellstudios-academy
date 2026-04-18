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

1. From this folder, run:
   ```bash
   php -S 127.0.0.1:8026 -t public
   ```
2. Open `http://127.0.0.1:8026`, create a few tasks, and confirm they are listed.
3. Check `storage/tasks.json` to confirm persistence.
4. Submit titles with quotes/symbols and confirm output remains escaped and stable.

← [Zero to PHP](../README.md)
