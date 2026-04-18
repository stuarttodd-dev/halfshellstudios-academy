<?php
declare(strict_types=1);

final class TaskRepository
{
    public function __construct(private string $storagePath)
    {
    }

    public function all(): array
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

    public function create(string $title, string $status): array
    {
        $items = $this->all();
        $item = ['id' => $this->nextId($items), 'title' => $title, 'status' => $status];
        $items[] = $item;
        $this->save($items);
        return $item;
    }

    private function save(array $items): void
    {
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->storagePath, json_encode($items, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    private function nextId(array $items): int
    {
        if ($items === []) {
            return 1;
        }
        return max(array_column($items, 'id')) + 1;
    }
}
