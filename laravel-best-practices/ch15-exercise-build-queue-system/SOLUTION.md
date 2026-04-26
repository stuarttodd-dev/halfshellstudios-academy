# Chapter 15 — Exercise: queue system (reference write-up)

**Course page:** [Build a small queue system](https://laravel.learnio.dev/learn/sections/chapter-15-queues-and-horizon/exercise-build-queue-system)

Use this as a **worked example** of what the course asks you to hand in. Replace names with your app’s, and point logs at real paths.

---

## 1. What the feature does (three sentences)

A web request enqueues a `ProcessInvoice` job with only the `invoice` id. The worker loads the row, and if the status is not already `processing`, it marks the invoice `paid` and writes an audit line. A second run with the same id must not double-charge: the first line of `handle` checks a status flag and returns early if work is already done.

## 2. How duplicate `handle` calls stay safe

`handle` uses `Invoice::query()->lockForUpdate()` in a transaction *or* a compare-and-swap on `status` (e.g. only transition from `pending` → `processing`). The exercise requires that removing the guard would double-write; that is the proof the idempotency is real, not a comment.

## 3. Local commands (database queue)

```env
QUEUE_CONNECTION=database
```

```bash
php artisan migrate
php artisan queue:work database --queue=default --tries=3 --backoff=5
php artisan queue:failed   # after forcing a throw once
php artisan queue:retry all
```

## 4. Deliberate failure and recovery

- Set `INVOICE_PROCESSING_FAIL_ONCE=1` in `.env` (or throw until a cache flag is set) so the first `handle` dies after logging.
- Confirm the job appears in `failed_jobs` (or the driver’s failure store).
- Fix the condition, `queue:retry` the id, and confirm the success side effect once.

## 5. What to monitor first in production

- `failed_jobs` count and rate, **then** queue latency (time from `dispatch` to `handle` start) **then** worker restarts. Alert on sustained failure rate before optimising mean wait time.

---

## Optional: minimal job skeleton (drop into a real `Invoice` app)

The course expects this inside your own repo; the snippet is only a reminder of serialisable IDs and a guard.

```php
public function __construct(private readonly int $invoiceId) {}

public function handle(): void
{
    $invoice = Invoice::query()->find($this->invoiceId);
    if ($invoice === null || $invoice->status !== 'pending') {
        return;
    }
    // perform side effect, then $invoice->update([...]);
}
```
