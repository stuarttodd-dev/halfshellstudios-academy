<?php
declare(strict_types=1);

$uploadsDir = __DIR__ . '/uploads';
$destination = $uploadsDir . '/' . basename('photo.jpg');

echo str_ends_with($destination, '/uploads/photo.jpg') ? 'upload' : 'no';
