<?php
declare(strict_types=1);

function errorResponse(string $code, string $message, int $status): array {
    http_response_code($status);
    return ['error' => ['code' => $code, 'message' => $message]];
}

function successResponse(array $data, int $status = 200): array {
    http_response_code($status);
    return $data;
}

header('Content-Type: application/json');
echo json_encode(successResponse(['smoke' => 'ok']), JSON_THROW_ON_ERROR);
