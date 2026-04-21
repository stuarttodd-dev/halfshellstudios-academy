<?php
declare(strict_types=1);

namespace DecentPhp\Ch7\Ex1\Csv;

final class CsvExporter
{
    /** @param list<array<int, mixed>> $rows */
    public function export(array $rows, string $path): void
    {
        $handle = fopen($path, 'w');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}
