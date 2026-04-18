<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/tasks') {
    echo "List screen";
} elseif ($path === '/tasks/create') {
    echo "Create form";
} elseif ($path === '/tasks/edit') {
    echo "Edit form";
} else {
    http_response_code(404);
    echo "Not found";
}
