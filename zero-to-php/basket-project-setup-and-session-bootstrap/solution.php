<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['basket']) || !is_array($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

$basketCount = array_sum(array_map(static fn(array $row): int => (int) $row['qty'], $_SESSION['basket']));

header('Content-Type: text/plain');
echo "Basket items: {$basketCount}\n";
