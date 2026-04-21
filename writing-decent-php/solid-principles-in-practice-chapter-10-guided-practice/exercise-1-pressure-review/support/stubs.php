<?php
declare(strict_types=1);

final class JsonResponse
{
    /** @param array<string, mixed> $data */
    public function __construct(public readonly array $data, public readonly int $status = 200) {}
}

/* ---------- in-memory user store ---------- */

final class UserRow
{
    public function __construct(
        public readonly int    $id,
        public readonly string $email,
        public readonly string $name,
        public readonly string $passwordHash,
    ) {}
}

final class InMemoryUserStore
{
    /** @var array<int, UserRow> */
    public array $users = [];
    private int  $next  = 1000;

    public function insert(string $email, string $name, string $passwordHash): UserRow
    {
        $row = new UserRow(id: $this->next++, email: $email, name: $name, passwordHash: $passwordHash);
        $this->users[$row->id] = $row;
        return $row;
    }

    public function find(int $id): UserRow
    {
        return $this->users[$id] ?? throw new RuntimeException("User {$id} not found");
    }
}

/* ---------- in-memory invoice store ---------- */

final class Invoice
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $amountInPence,
    ) {}
}

final class InMemoryInvoiceStore
{
    /** @var list<Invoice> */
    public array $invoices = [];
    private int  $next     = 5000;

    public function save(int $userId, int $amountInPence): Invoice
    {
        $invoice = new Invoice(id: $this->next++, userId: $userId, amountInPence: $amountInPence);
        $this->invoices[] = $invoice;
        return $invoice;
    }
}

/* ---------- recording fakes ---------- */

final class RecordingMailer
{
    /** @var list<array{to: string, subject: string}> */
    public array $sent = [];

    public function send(string $to, string $subject): void
    {
        $this->sent[] = ['to' => $to, 'subject' => $subject];
    }
}

final class RecordingAuditLog
{
    /** @var list<string> */
    public array $entries = [];

    public function record(string $event): void
    {
        $this->entries[] = $event;
    }
}

/**
 * Stand-in for the Stripe SDK. Records the charges instead of hitting the
 * network, returns a fake charge id.
 */
final class FakeStripe
{
    /** @var list<array{user_id: int, amount: int, charge_id: string}> */
    public array $charges = [];
    private int  $next    = 8000;

    public function chargeMonthly(int $userId, int $amountInPence): string
    {
        $chargeId = sprintf('ch_%05d', $this->next++);
        $this->charges[] = ['user_id' => $userId, 'amount' => $amountInPence, 'charge_id' => $chargeId];
        return $chargeId;
    }
}
