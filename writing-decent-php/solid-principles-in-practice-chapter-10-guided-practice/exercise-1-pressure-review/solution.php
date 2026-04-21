<?php
declare(strict_types=1);

require_once __DIR__ . '/support/stubs.php';

/**
 * Pressure review (priority order):
 *
 *  1. SRP. Three reasons for `CustomerService` to change — registration
 *     logic, billing logic, finance reporting — each driven by a
 *     different stakeholder, each on a different release cadence.
 *     Action: split into three classes named after the use case.
 *  2. ISP. Every consumer of `CustomerService` drags Stripe, mailer,
 *     and audit log into its dependency graph even when it just wants
 *     to register a user. Splitting fixes ISP for free.
 *  3. (DIP, OCP, LSP do not press hard here — there is no growing
 *      polymorphism, no missing abstraction, no inheritance.)
 */

/** Use case 1 — sign-up flow. Knows about users, mailer, audit. */
final class RegisterCustomer
{
    public function __construct(
        private InMemoryUserStore $users,
        private RecordingMailer   $mailer,
        private RecordingAuditLog $audit,
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
}

/** Use case 2 — recurring billing. Knows about Stripe, invoices, mailer, audit. */
final class ChargeMonthlySubscription
{
    public function __construct(
        private InMemoryUserStore    $users,
        private InMemoryInvoiceStore $invoices,
        private FakeStripe           $stripe,
        private RecordingMailer      $mailer,
        private RecordingAuditLog    $audit,
    ) {}

    public function chargeMonthly(int $userId, int $amountInPence): void
    {
        $user     = $this->users->find($userId);
        $chargeId = $this->stripe->chargeMonthly($user->id, $amountInPence);
        $invoice  = $this->invoices->save($user->id, $amountInPence);

        $this->mailer->send($user->email, "Receipt for invoice #{$invoice->id} (charge {$chargeId})");
        $this->audit->record("user.charged:{$user->id}:{$invoice->id}");
    }
}

/** Use case 3 — finance export. Knows about users only. */
final class ExportCustomersForFinance
{
    public function __construct(private InMemoryUserStore $users) {}

    /** @return list<array<string, string|int>> */
    public function forMonth(int $month): array
    {
        $rows = [];
        foreach ($this->users->users as $user) {
            $rows[] = ['id' => $user->id, 'email' => $user->email, 'name' => $user->name, 'month' => $month];
        }

        return $rows;
    }
}

/* ---------- driver (identical to starter.php) ---------- */

$users    = new InMemoryUserStore();
$invoices = new InMemoryInvoiceStore();
$stripe   = new FakeStripe();
$mailer   = new RecordingMailer();
$audit    = new RecordingAuditLog();

$register = new RegisterCustomer($users, $mailer, $audit);
$charge   = new ChargeMonthlySubscription($users, $invoices, $stripe, $mailer, $audit);
$export   = new ExportCustomersForFinance($users);

$response = $register->register(['email' => 'alice@example.com', 'name' => 'Alice', 'password' => 'sekret!']);
$charge->chargeMonthly(userId: 1000, amountInPence: 999);
$rows = $export->forMonth(month: 4);

printf("register   -> %d %s\n",  $response->status, json_encode($response->data));
printf("invoices   -> %s\n",     json_encode($invoices->invoices));
printf("mailer     -> %s\n",     json_encode($mailer->sent));
printf("audit      -> %s\n",     json_encode($audit->entries));
printf("export     -> %s\n",     json_encode($rows));
