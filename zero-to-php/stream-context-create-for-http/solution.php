<?php
declare(strict_types=1);

$p = __DIR__ . '/j.txt';
file_put_contents($p, '{"z":2}');

$ctx = stream_context_create(['file' => ['mode' => 'r']]);
$raw = file_get_contents($p, false, $ctx);
unlink($p);

$d = json_decode((string) $raw, true, 512, JSON_THROW_ON_ERROR);
echo $d['z'];
