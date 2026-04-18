<?php
declare(strict_types=1);

function validateCreate(array $input): array {
    $errors = [];
    $title = trim((string) ($input['title'] ?? ''));
    $status = (string) ($input['status'] ?? '');
    $allowedStatus = ['todo', 'doing', 'done'];

    if ($title === '') {
        $errors['title'] = 'Title is required.';
    }
    if (!in_array($status, $allowedStatus, true)) {
        $errors['status'] = 'Status must be todo, doing, or done.';
    }

    return $errors;
}

$input = ['title' => 'Ship release', 'status' => 'doing'];
$errors = validateCreate($input);
echo $errors === [] ? "valid\n" : "invalid\n";
