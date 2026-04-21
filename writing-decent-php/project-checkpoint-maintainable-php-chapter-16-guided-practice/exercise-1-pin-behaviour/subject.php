<?php
declare(strict_types=1);

/**
 * The starter InvoiceGenerator, preserved byte-for-byte from the chapter
 * brief, together with the *minimum* scaffolding needed to make it run
 * in isolation:
 *
 *   - An in-memory `DB` class that mimics the two static methods the
 *     starter uses (`DB::select`, `DB::insert`). This lives only inside
 *     the test harness — we have not touched the starter.
 *   - An `ini_set('sendmail_path', '/bin/true')` in the test file, so
 *     that the starter's global `mail()` call returns true without
 *     actually posting anything.
 *   - A temp directory for the starter's `/tmp/invoice-$id.pdf` writes,
 *     cleaned up between cases.
 *
 * The InvoiceGenerator itself is unchanged from the chapter brief.
 * That is load-bearing: Exercise 1 is about pinning behaviour, and you
 * cannot pin behaviour you have already modified.
 */

// ---- Minimum DB stub so the starter can run ---------------------------------

/**
 * A two-table in-memory fake of the DB facade the starter calls into.
 *
 * We only implement the two shapes of query the starter actually makes:
 *   - SELECT * FROM clients WHERE active = 1
 *   - SELECT * FROM sessions WHERE client_id = ? AND date BETWEEN ? AND ?
 *   - INSERT INTO invoices (...)
 *
 * That is enough. A characterisation test is about pinning the output
 * of the subject under representative input; it is not a DB driver.
 */
final class DB
{
    /** @var array<int, object{id:int, name:string, email:string, plan:string, country:string, active:int}> */
    public static array $clients = [];

    /** @var array<int, object{id:int, client_id:int, date:string, hours:int|float}> */
    public static array $sessions = [];

    /** @var list<array{id:int, client_id:int, total:int, period:string}> */
    public static array $invoices = [];

    private static int $nextInvoiceId = 0;

    public static function reset(): void
    {
        self::$clients = [];
        self::$sessions = [];
        self::$invoices = [];
        self::$nextInvoiceId = 0;
    }

    /** @return array<int, object> */
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

    /** Returns the id of the inserted row, as the starter assumes. */
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

// ---- The starter, unchanged -------------------------------------------------

class InvoiceGenerator
{
    public function generate()
    {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        $clients = DB::select('SELECT * FROM clients WHERE active = 1');

        $generated = [];
        foreach ($clients as $client) {
            $sessions = DB::select('SELECT * FROM sessions WHERE client_id = ? AND date BETWEEN ? AND ?', [$client->id, $start, $end]);
            if (count($sessions) === 0) continue;

            $hours = 0;
            foreach ($sessions as $s) $hours += $s->hours;
            $rate = $client->plan === 'premium' ? 150 : 100;
            $sub = $hours * $rate;
            $vat = $client->country === 'GB' ? round($sub * 0.20) : 0;
            $tot = $sub + $vat;

            $invoiceId = DB::insert('INSERT INTO invoices (client_id, total, period) VALUES (?, ?, ?)', [$client->id, $tot, date('Y-m')]);

            $body = "Invoice #$invoiceId\nClient: {$client->name}\nHours: $hours\nTotal: $tot";
            $pdf = "/tmp/invoice-$invoiceId.pdf";
            file_put_contents($pdf, $body);

            mail($client->email, "Invoice for " . date('F Y'), "Please find your invoice attached.");

            $generated[] = $invoiceId;
        }

        return $generated;
    }
}
