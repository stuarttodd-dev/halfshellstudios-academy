<?php
declare(strict_types=1);

/**
 * Exercise 2 — "Name the rules".
 *
 * Same DB stub as Exercise 1. The only thing that changes here is the
 * InvoiceGenerator itself, and only in one respect: the two buried
 * ternaries
 *
 *   $client->plan === 'premium' ? 150 : 100
 *   $client->country === 'GB'   ? round($sub * 0.20) : 0
 *
 * have moved into named rule classes with named constants. No
 * structural changes, no new collaborators, no new side-effects. The
 * characterisation test from Exercise 1 must still pass unchanged.
 *
 * The value of this step: the rules have *names* now. A reader can
 * search for `HourlyRate` or `VatRule` and find the single definition.
 * The magic numbers `150`, `100`, `0.20` and the magic strings
 * `'premium'`, `'GB'` are gone. Those were the two concrete rules
 * hiding inside `generate()`, and they are the two that are most
 * likely to change independently of the surrounding orchestration.
 */

// ---- Minimum DB stub (unchanged from Exercise 1) ---------------------------

final class DB
{
    public static array $clients = [];
    public static array $sessions = [];
    public static array $invoices = [];
    private static int $nextInvoiceId = 0;

    public static function reset(): void
    {
        self::$clients = [];
        self::$sessions = [];
        self::$invoices = [];
        self::$nextInvoiceId = 0;
    }

    public static function select(string $sql, array $bindings = []): array
    {
        if (str_contains($sql, 'FROM clients')) {
            return array_values(array_filter(self::$clients, static fn (object $c) => (int) $c->active === 1));
        }
        if (str_contains($sql, 'FROM sessions')) {
            [$clientId, $start, $end] = $bindings;
            return array_values(array_filter(
                self::$sessions,
                static fn (object $s) => (int) $s->client_id === (int) $clientId
                    && strcmp($s->date, (string) $start) >= 0
                    && strcmp($s->date, (string) $end) <= 0,
            ));
        }
        throw new RuntimeException('DB::select stub does not know this query: ' . $sql);
    }

    public static function insert(string $sql, array $bindings): int
    {
        if (str_contains($sql, 'INTO invoices')) {
            [$clientId, $total, $period] = $bindings;
            $id = ++self::$nextInvoiceId;
            self::$invoices[] = [
                'id'        => $id,
                'client_id' => (int) $clientId,
                'total'     => (int) $total,
                'period'    => (string) $period,
            ];
            return $id;
        }
        throw new RuntimeException('DB::insert stub does not know this query: ' . $sql);
    }
}

// ---- The named rules --------------------------------------------------------

/**
 * The hourly rate a client is billed at, given their plan.
 *
 * Both the magic string `'premium'` and the magic numbers `150` / `100`
 * had no name before this class. They do now, and there is exactly one
 * place in the codebase to update them.
 */
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

/**
 * The VAT rule applied to a subtotal, given a client's country.
 *
 * GB customers are charged 20% VAT; everyone else is charged none.
 * Rounding policy (`round`) is preserved from the starter to keep the
 * characterisation test green — this is a rename, not a rewrite.
 */
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

// ---- InvoiceGenerator, now using the named rules ----------------------------

class InvoiceGenerator
{
    public function __construct(
        private HourlyRate $hourlyRate = new HourlyRate(),
        private VatRule $vatRule = new VatRule(),
    ) {
    }

    public function generate()
    {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        $clients = DB::select('SELECT * FROM clients WHERE active = 1');

        $generated = [];
        foreach ($clients as $client) {
            $sessions = DB::select(
                'SELECT * FROM sessions WHERE client_id = ? AND date BETWEEN ? AND ?',
                [$client->id, $start, $end],
            );
            if (count($sessions) === 0) continue;

            $hours = 0;
            foreach ($sessions as $s) $hours += $s->hours;

            $sub = $hours * $this->hourlyRate->forClient($client);
            $vat = $this->vatRule->amountFor($sub, $client);
            $tot = $sub + $vat;

            $invoiceId = DB::insert(
                'INSERT INTO invoices (client_id, total, period) VALUES (?, ?, ?)',
                [$client->id, $tot, date('Y-m')],
            );

            $body = "Invoice #$invoiceId\nClient: {$client->name}\nHours: $hours\nTotal: $tot";
            $pdf = "/tmp/invoice-$invoiceId.pdf";
            file_put_contents($pdf, $body);

            mail($client->email, "Invoice for " . date('F Y'), "Please find your invoice attached.");

            $generated[] = $invoiceId;
        }

        return $generated;
    }
}
