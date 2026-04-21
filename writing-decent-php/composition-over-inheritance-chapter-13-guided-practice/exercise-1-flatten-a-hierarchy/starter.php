<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Three-level hierarchy:
 *
 *     AbstractApiController
 *         └─ AbstractAuthenticatedApiController
 *                 └─ OrderApiController
 *
 * The leaf inherits two layers of "helpers" — JSON formatting and
 * authentication — neither of which it can choose, override, or test
 * independently. To understand `OrderApiController` you have to read
 * three classes top-to-bottom, including `protected` state on every
 * one of them.
 */

abstract class AbstractApiController
{
    /** @param array<string, mixed> $data */
    protected function jsonOk(array $data): JsonResponse
    {
        return new JsonResponse($data, 200);
    }

    protected function jsonError(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }
}

abstract class AbstractAuthenticatedApiController extends AbstractApiController
{
    protected ?User $currentUser = null;

    public function __construct(protected InMemoryUserDirectory $users) {}

    protected function authenticate(Request $request): ?JsonResponse
    {
        $token = $request->headers['Authorization'] ?? '';

        $this->currentUser = $this->users->userForToken($token);
        if ($this->currentUser === null) {
            return $this->jsonError('unauthorised', 401);
        }

        return null;
    }
}

final class OrderApiController extends AbstractAuthenticatedApiController
{
    public function __construct(
        InMemoryUserDirectory       $users,
        private InMemoryOrderStore  $orders,
    ) {
        parent::__construct($users);
    }

    public function __invoke(Request $request): JsonResponse
    {
        if ($failure = $this->authenticate($request)) {
            return $failure;
        }

        $orders = $this->orders->ordersFor($this->currentUser->id);

        return $this->jsonOk(['orders' => $orders]);
    }
}

/* ---------- driver ---------- */

$users      = new InMemoryUserDirectory();
$orders     = new InMemoryOrderStore();
$controller = new OrderApiController($users, $orders);

$ok   = $controller(new Request(['Authorization' => 'tok_alice']));
$bad  = $controller(new Request(['Authorization' => 'tok_invalid']));

echo "200 -> {$ok->status} " . json_encode($ok->data) . "\n";
echo "401 -> {$bad->status} " . json_encode($bad->data) . "\n";
