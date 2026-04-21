<?php
declare(strict_types=1);

/**
 * Exercise 3 — "Collaborate, don't query".
 *
 * Starting from the Exercise 2 subject (which has HourlyRate and VatRule
 * already extracted), this step removes *all* of InvoiceGenerator's
 * hidden dependencies on the world:
 *
 *   - Static `DB::select` / `DB::insert` calls become
 *     `ClientRepository`, `SessionRepository`, `InvoiceRepository`
 *     interfaces, injected via the constructor.
 *   - `file_put_contents("/tmp/invoice-$id.pdf", $body)` becomes
 *     an `InvoiceStore` interface.
 *   - `mail($client->email, ...)` becomes an `InvoiceNotifier`
 *     interface.
 *   - `date('Y-m-01')` / `date('Y-m-t')` / `date('Y-m')` /
 *     `date('F Y')` become a `BillingPeriod` value object,
 *     computed from an injected `Clock`. The generator no longer
 *     calls `date()` at all.
 *
 * What this buys us:
 *
 *   - Full deterministic unit-test coverage without DB, filesystem,
 *     SMTP, or wall-clock access (see `unit_test.php`).
 *   - A generator that is obviously a *policy* class: it orchestrates
 *     the rules and the collaborators, and does no I/O itself.
 *   - Change-friendly dependencies: swapping Postgres for MySQL, or
 *     `mail()` for Mailgun, or `file_put_contents` for S3, is a new
 *     adapter class — never a change to InvoiceGenerator.
 *
 * We keep the same two characterisation scenarios from Exercise 1, now
 * driven entirely through the in-memory collaborators below. They
 * produce byte-identical invoices and identical "stored" PDF bodies,
 * which is how we prove the refactor preserves behaviour.
 */

// ---- Domain value objects ---------------------------------------------------

/**
 * A dated Y-M-01..Y-M-t billing period, with a human-readable month
 * name. All the `date(...)` calls from the starter live in one place
 * now, next to their intent.
 */
final class BillingPeriod
{
    public function __construct(
        public readonly string $start,     // 'YYYY-MM-01'
        public readonly string $end,       // last day of month
        public readonly string $yearMonth, // 'YYYY-MM'
        public readonly string $label,     // 'April 2026'
    ) {
    }

    public static function fromClock(Clock $clock): self
    {
        $now = $clock->now();
        return new self(
            start:     $now->format('Y-m-01'),
            end:       $now->format('Y-m-t'),
            yearMonth: $now->format('Y-m'),
            label:     $now->format('F Y'),
        );
    }
}

// ---- Collaborator ports -----------------------------------------------------

interface Clock
{
    public function now(): DateTimeImmutable;
}

interface ClientRepository
{
    /** @return list<object> */
    public function activeClients(): array;
}

interface SessionRepository
{
    /** @return list<object> */
    public function forClientBetween(int $clientId, string $start, string $end): array;
}

interface InvoiceRepository
{
    public function create(int $clientId, int $total, string $period): int;
}

interface InvoiceStore
{
    /** Returns an opaque locator (path, URL, object key) for the stored artefact. */
    public function store(int $invoiceId, string $body): string;
}

interface InvoiceNotifier
{
    public function notify(object $client, int $invoiceId, string $periodLabel): void;
}

// ---- Rules (carried over from Exercise 2) -----------------------------------

final class HourlyRate
{
    public const PREMIUM_PLAN        = 'premium';
    public const PREMIUM_RATE_PENCE  = 150;
    public const STANDARD_RATE_PENCE = 100;

    public function forClient(object $client): int
    {
        return $client->plan === self::PREMIUM_PLAN
            ? self::PREMIUM_RATE_PENCE
            : self::STANDARD_RATE_PENCE;
    }
}

final class VatRule
{
    public const VAT_COUNTRY         = 'GB';
    public const GB_VAT_RATE         = 0.20;
    public const NO_VAT_AMOUNT_PENCE = 0;

    public function amountFor(int $subtotal, object $client): int
    {
        if ($client->country !== self::VAT_COUNTRY) {
            return self::NO_VAT_AMOUNT_PENCE;
        }

        return (int) round($subtotal * self::GB_VAT_RATE);
    }
}

// ---- The InvoiceGenerator -- now a policy class, no hidden I/O -------------

final class InvoiceGenerator
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly SessionRepository $sessions,
        private readonly InvoiceRepository $invoices,
        private readonly InvoiceStore $store,
        private readonly InvoiceNotifier $notifier,
        private readonly HourlyRate $hourlyRate = new HourlyRate(),
        private readonly VatRule $vatRule = new VatRule(),
    ) {
    }

    /** @return list<int> */
    public function generate(BillingPeriod $period): array
    {
        $generated = [];
        foreach ($this->clients->activeClients() as $client) {
            $sessions = $this->sessions->forClientBetween($client->id, $period->start, $period->end);
            if ($sessions === []) {
                continue;
            }

            $hours    = $this->totalHours($sessions);
            $subtotal = $hours * $this->hourlyRate->forClient($client);
            $total    = $subtotal + $this->vatRule->amountFor($subtotal, $client);

            $invoiceId = $this->invoices->create($client->id, $total, $period->yearMonth);

            $this->store->store($invoiceId, $this->body($invoiceId, $client, $hours, $total));
            $this->notifier->notify($client, $invoiceId, $period->label);

            $generated[] = $invoiceId;
        }

        return $generated;
    }

    /** @param list<object> $sessions */
    private function totalHours(array $sessions): int|float
    {
        $total = 0;
        foreach ($sessions as $session) {
            $total += $session->hours;
        }
        return $total;
    }

    private function body(int $invoiceId, object $client, int|float $hours, int $total): string
    {
        return "Invoice #{$invoiceId}\nClient: {$client->name}\nHours: {$hours}\nTotal: {$total}";
    }
}

// ---- In-memory adapters, for tests and local runs --------------------------

final class FixedClock implements Clock
{
    public function __construct(private readonly DateTimeImmutable $now) {}
    public function now(): DateTimeImmutable { return $this->now; }
}

final class InMemoryClientRepository implements ClientRepository
{
    /** @param list<object> $clients */
    public function __construct(private array $clients) {}

    public function activeClients(): array
    {
        return array_values(array_filter($this->clients, static fn (object $c) => (int) $c->active === 1));
    }
}

final class InMemorySessionRepository implements SessionRepository
{
    /** @param list<object> $sessions */
    public function __construct(private array $sessions) {}

    public function forClientBetween(int $clientId, string $start, string $end): array
    {
        return array_values(array_filter(
            $this->sessions,
            static fn (object $s) => (int) $s->client_id === $clientId
                && strcmp($s->date, $start) >= 0
                && strcmp($s->date, $end)   <= 0,
        ));
    }
}

final class InMemoryInvoiceRepository implements InvoiceRepository
{
    /** @var list<array{id:int, client_id:int, total:int, period:string}> */
    public array $saved = [];
    private int $nextId = 0;

    public function create(int $clientId, int $total, string $period): int
    {
        $id = ++$this->nextId;
        $this->saved[] = ['id' => $id, 'client_id' => $clientId, 'total' => $total, 'period' => $period];
        return $id;
    }
}

final class RecordingInvoiceStore implements InvoiceStore
{
    /** @var array<int, string> */
    public array $bodies = [];

    public function store(int $invoiceId, string $body): string
    {
        $this->bodies[$invoiceId] = $body;
        return "memory://invoice-{$invoiceId}";
    }
}

final class RecordingInvoiceNotifier implements InvoiceNotifier
{
    /** @var list<array{email:string, invoice_id:int, period:string}> */
    public array $sent = [];

    public function notify(object $client, int $invoiceId, string $periodLabel): void
    {
        $this->sent[] = [
            'email'      => $client->email,
            'invoice_id' => $invoiceId,
            'period'     => $periodLabel,
        ];
    }
}
