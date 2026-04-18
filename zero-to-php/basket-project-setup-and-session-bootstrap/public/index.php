<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['basket']) || !is_array($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

$count = array_sum(array_map(static fn(array $row): int => (int) ($row['qty'] ?? 0), $_SESSION['basket']));
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Basket Setup</title></head>
<body>
<h1>Basket setup and session bootstrap</h1>
<p>Basket item count: <?= (int) $count ?></p>
</body>
</html>
