<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Domain exceptions: each one names a specific failure mode the caller
 * may want to react to differently. They carry the data the boundary
 * needs to render a useful response, not the message the human will see.
 */
final class AccountNotFoundException extends \DomainException
{
    public function __construct(public readonly int $accountId, public readonly string $role)
    {
        parent::__construct("{$role} account #{$accountId} not found");
    }
}

final class InsufficientBalanceException extends \DomainException
{
    public function __construct(
        public readonly int $accountId,
        public readonly int $availableInPence,
        public readonly int $requestedInPence,
    ) {
        parent::__construct(
            "Account #{$accountId} has {$availableInPence}p but {$requestedInPence}p was requested"
        );
    }
}

final class TransferFunds
{
    public function __construct(private InMemoryAccountRepository $accounts) {}

    public function transfer(int $fromAccount, int $toAccount, int $amountInPence): void
    {
        $from = $this->accounts->byId($fromAccount)
            ?? throw new AccountNotFoundException($fromAccount, 'source');

        $to = $this->accounts->byId($toAccount)
            ?? throw new AccountNotFoundException($toAccount, 'destination');

        if ($from->balanceInPence < $amountInPence) {
            throw new InsufficientBalanceException(
                accountId:        $from->id,
                availableInPence: $from->balanceInPence,
                requestedInPence: $amountInPence,
            );
        }

        $this->accounts->debit($from, $amountInPence);
        $this->accounts->credit($to, $amountInPence);
    }
}

/**
 * The translation layer. The boundary, not the use case, decides what
 * each failure means in HTTP terms — a missing account is a 404, a
 * busted balance is a 422 ("understood, but cannot be processed").
 * Anything we did not name explicitly is a real bug; let it propagate.
 */
final class TransferFundsController
{
    public function __construct(private TransferFunds $useCase) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->useCase->transfer(
                (int) $request->input('from'),
                (int) $request->input('to'),
                (int) $request->input('amount'),
            );
        } catch (AccountNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'account_not_found', 'role' => $e->role, 'account_id' => $e->accountId],
                404,
            );
        } catch (InsufficientBalanceException $e) {
            return new JsonResponse(
                [
                    'error'      => 'insufficient_balance',
                    'account_id' => $e->accountId,
                    'available'  => $e->availableInPence,
                    'requested'  => $e->requestedInPence,
                ],
                422,
            );
        }

        return new JsonResponse(['status' => 'ok']);
    }
}

/* ---------- driver (same scenarios as starter.php) ---------- */

function fresh(): TransferFundsController
{
    return new TransferFundsController(new TransferFunds(new InMemoryAccountRepository([
        new Account(id: 1, balanceInPence: 10_000),
        new Account(id: 2, balanceInPence:    500),
    ])));
}

$cases = [
    'happy'                 => ['from' => 1, 'to' => 2, 'amount' => 2_000],
    'from missing'          => ['from' => 9, 'to' => 2, 'amount' => 2_000],
    'to missing'            => ['from' => 1, 'to' => 9, 'amount' => 2_000],
    'insufficient balance'  => ['from' => 2, 'to' => 1, 'amount' => 2_000],
];

foreach ($cases as $label => $payload) {
    $response = (fresh())(new Request($payload));
    printf("%-22s -> %d %s\n", $label, $response->status, json_encode($response->data));
}
