<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

final class TransferFunds
{
    public function __construct(private InMemoryAccountRepository $accounts) {}

    public function transfer(int $fromAccount, int $toAccount, int $amountInPence): void
    {
        $from = $this->accounts->byId($fromAccount);
        if ($from === null) { throw new \Exception('From account not found'); }
        $to = $this->accounts->byId($toAccount);
        if ($to === null)   { throw new \Exception('To account not found'); }

        if ($from->balanceInPence < $amountInPence) {
            throw new \Exception('Insufficient balance');
        }

        $this->accounts->debit($from, $amountInPence);
        $this->accounts->credit($to, $amountInPence);
    }
}

/**
 * Original controller does what every "all errors are equal" controller
 * does: catch \Throwable, return 500, lose all the diagnostic information.
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

            return new JsonResponse(['status' => 'ok']);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}

/* ---------- driver ---------- */

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
