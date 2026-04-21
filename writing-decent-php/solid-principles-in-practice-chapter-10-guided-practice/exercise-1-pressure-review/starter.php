<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * One class, three completely unrelated jobs:
 *   1) handle a registration HTTP request (validate, hash, insert, email, audit)
 *   2) run the monthly billing job (load user, call Stripe, save invoice, email receipt)
 *   3) build a CSV export for finance
 *
 * Every consumer of this class drags all of the above with it.
 */
final class CustomerService
{
    public function __construct(
        public InMemoryUserStore    $users,
        public InMemoryInvoiceStore $invoices,
        public FakeStripe           $stripe,
        public RecordingMailer      $mailer,
        public RecordingAuditLog    $audit,
    ) {}

    /** @param array<string, string> $req */
    public function register(array $req): JsonResponse
    {
        if (empty($req['email']) || empty($req['name']) || empty($req['password'])) {
            return new JsonResponse(['error' => 'missing fields'], 422);
        }

        $hash = password_hash($req['password'], PASSWORD_BCRYPT);
        $user = $this->users->insert($req['email'], $req['name'], $hash);

        $this->mailer->send($user->email, "Welcome, {$user->name}!");
        $this->audit->record("user.registered:{$user->id}");

        return new JsonResponse(['id' => $user->id], 201);
    }

    public function chargeMonthly(int $userId, int $amountInPence): void
    {
        $user      = $this->users->find($userId);
        $chargeId  = $this->stripe->chargeMonthly($user->id, $amountInPence);
        $invoice   = $this->invoices->save($user->id, $amountInPence);

        $this->mailer->send($user->email, "Receipt for invoice #{$invoice->id} (charge {$chargeId})");
        $this->audit->record("user.charged:{$user->id}:{$invoice->id}");
    }

    /** @return list<array<string, string|int>> */
    public function exportForFinance(int $month): array
    {
        $rows = [];
        foreach ($this->users->users as $user) {
            $rows[] = ['id' => $user->id, 'email' => $user->email, 'name' => $user->name, 'month' => $month];
        }

        return $rows;
    }
}

/* ---------- driver ---------- */

$users    = new InMemoryUserStore();
$invoices = new InMemoryInvoiceStore();
$stripe   = new FakeStripe();
$mailer   = new RecordingMailer();
$audit    = new RecordingAuditLog();

$service = new CustomerService($users, $invoices, $stripe, $mailer, $audit);

$response = $service->register(['email' => 'alice@example.com', 'name' => 'Alice', 'password' => 'sekret!']);
$service->chargeMonthly(userId: 1000, amountInPence: 999);
$rows = $service->exportForFinance(month: 4);

printf("register   -> %d %s\n",  $response->status, json_encode($response->data));
printf("invoices   -> %s\n",     json_encode($invoices->invoices));
printf("mailer     -> %s\n",     json_encode($mailer->sent));
printf("audit      -> %s\n",     json_encode($audit->entries));
printf("export     -> %s\n",     json_encode($rows));
