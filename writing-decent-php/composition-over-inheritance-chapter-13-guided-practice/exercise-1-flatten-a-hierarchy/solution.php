<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Flatten the hierarchy by turning each "abstract helper layer" into a
 * collaborator the controller takes by injection.
 *
 *   - JSON formatting → `JsonResponder`        (stateless helper)
 *   - Authentication  → `RequestAuthenticator` (returns a User or null)
 *
 * The controller now has zero parents. Read it top-to-bottom in one
 * file and you know exactly what it does. Both collaborators are
 * directly testable without instantiating a controller, and every
 * controller in the codebase can choose its own auth/response policy
 * — adding a "service-token-only" admin endpoint no longer requires
 * a fourth abstract class.
 */

final class JsonResponder
{
    /** @param array<string, mixed> $data */
    public function ok(array $data): JsonResponse
    {
        return new JsonResponse($data, 200);
    }

    public function error(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }
}

final class RequestAuthenticator
{
    public function __construct(private InMemoryUserDirectory $users) {}

    public function userFor(Request $request): ?User
    {
        $token = $request->headers['Authorization'] ?? '';

        return $this->users->userForToken($token);
    }
}

final class OrderApiController
{
    public function __construct(
        private RequestAuthenticator $auth,
        private JsonResponder        $json,
        private InMemoryOrderStore   $orders,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->auth->userFor($request);
        if ($user === null) {
            return $this->json->error('unauthorised', 401);
        }

        $orders = $this->orders->ordersFor($user->id);

        return $this->json->ok(['orders' => $orders]);
    }
}

/* ---------- driver (identical observable output to starter.php) ---------- */

$users      = new InMemoryUserDirectory();
$orders     = new InMemoryOrderStore();
$controller = new OrderApiController(
    new RequestAuthenticator($users),
    new JsonResponder(),
    $orders,
);

$ok  = $controller(new Request(['Authorization' => 'tok_alice']));
$bad = $controller(new Request(['Authorization' => 'tok_invalid']));

echo "200 -> {$ok->status} " . json_encode($ok->data) . "\n";
echo "401 -> {$bad->status} " . json_encode($bad->data) . "\n";
