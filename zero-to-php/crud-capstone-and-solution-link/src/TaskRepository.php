<?php
declare(strict_types=1);

final class TaskRepository
{
    public function __construct(private string $storagePath)
    {
    }

    public function all(): array
    {
        return $this->load();
    }

    public function find(int $id): ?array
    {
        foreach ($this->load() as $task) {
            if ((int) $task['id'] === $id) {
                return $task;
            }
        }

        return null;
    }

    public function create(array $data): array
    {
        $tasks = $this->load();
        $task = [
            'id' => $this->nextId($tasks),
            'title' => $data['title'],
            'status' => $data['status'],
            'due_date' => $data['due_date'] ?: null,
        ];
        $tasks[] = $task;
        $this->save($tasks);

        return $task;
    }

    public function update(int $id, array $data): bool
    {
        $tasks = $this->load();
        foreach ($tasks as &$task) {
            if ((int) $task['id'] === $id) {
                $task['title'] = $data['title'];
                $task['status'] = $data['status'];
                $task['due_date'] = $data['due_date'] ?: null;
                $this->save($tasks);
                return true;
            }
        }

        return false;
    }

    public function delete(int $id): bool
    {
        $tasks = $this->load();
        $before = count($tasks);
        $tasks = array_values(array_filter($tasks, static fn(array $task): bool => (int) $task['id'] !== $id));

        if (count($tasks) === $before) {
            return false;
        }

        $this->save($tasks);
        return true;
    }

    private function load(): array
    {
        if (!is_file($this->storagePath)) {
            return [];
        }

        $json = file_get_contents($this->storagePath);
        if (!is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function save(array $tasks): void
    {
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->storagePath, json_encode($tasks, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    private function nextId(array $tasks): int
    {
        if ($tasks === []) {
            return 1;
        }

        return max(array_column($tasks, 'id')) + 1;
    }
}
