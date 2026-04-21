<?php
declare(strict_types=1);

/**
 * Shared driver for both starter.php and solution.php. The framework's
 * top-level handler is faked here as a try/catch around the controller —
 * exactly what Laravel/Symfony do with uncaught exceptions in production:
 * log them, return 500.
 */

/** @return array{status: int, body: array<string, mixed>, framework_caught: bool} */
function invokeViaFrameworkTopLevelHandler(callable $controller, Request $request): array
{
    try {
        $response = $controller($request);

        return [
            'status'           => $response->status,
            'body'             => $response->data,
            'framework_caught' => false,
        ];
    } catch (\Throwable $e) {
        Log::error('top-level: ' . $e->getMessage());

        return [
            'status'           => 500,
            'body'             => ['error' => 'Internal Server Error'],
            'framework_caught' => true,
        ];
    }
}

function runScenarios(callable $controller): void
{
    $cases = ['happy', 'customer-missing', 'already-invoiced', 'bad-input', 'system-failure'];

    foreach ($cases as $case) {
        Log::reset();
        $result = invokeViaFrameworkTopLevelHandler($controller, new Request(['case' => $case]));

        printf("%-18s -> %d %s\n", $case, $result['status'], json_encode($result['body']));
        if ($result['framework_caught']) {
            printf("                     (framework top-level handler caught it; logged: \"%s\")\n",
                Log::$errors[count(Log::$errors) - 1] ?? '');
        } elseif (Log::$errors !== []) {
            printf("                     (controller logged: \"%s\")\n", Log::$errors[0]);
        }
    }
}
