<?php
declare(strict_types=1);

final class ItemStore
{
    public function __construct(private string $path)
    {
    }

    public function all(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $json = file_get_contents($this->path);
        if (!is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function create(string $name, int $price): array
    {
        $items = $this->all();
        $item = [
            'id' => $this->nextId($items),
            'name' => $name,
            'price' => $price,
        ];
        $items[] = $item;
        $this->save($items);

        return $item;
    }

    private function save(array $items): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->path, json_encode($items, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    private function nextId(array $items): int
    {
        if ($items === []) {
            return 1;
        }

        return max(array_column($items, 'id')) + 1;
    }
}
