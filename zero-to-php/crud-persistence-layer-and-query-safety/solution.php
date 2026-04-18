<?php
declare(strict_types=1);

final class TaskRepository {
    public function __construct(private PDO $pdo) {}

    public function list(): array {
        return $this->pdo
            ->query('SELECT id, title, status FROM tasks ORDER BY id DESC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $title, string $status): void {
        $stmt = $this->pdo->prepare('INSERT INTO tasks (title, status) VALUES (:title, :status)');
        $stmt->execute(['title' => $title, 'status' => $status]);
    }
}

echo "repository_ready\n";
