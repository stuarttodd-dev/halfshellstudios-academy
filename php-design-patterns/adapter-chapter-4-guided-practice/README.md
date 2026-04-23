# Chapter 4 — Adapter (guided practice)

Three places that touch a foreign system. Two of them want an Adapter
to do the translation; one of them does not.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Email service | SDK uses `to_addr` / `subj` / `msg_html`; result has `status_code` | **Adapter fits** — translate to a domain `Mailer::send($to, $from, $subject, $htmlBody)` |
| 2 — Geocoding | SDK already exposes `isValid()` | **Trap.** Same shape, same vocabulary. DI through a thin domain interface, no translation |
| 3 — Legacy reporting DB | Raw SQL string, dollars-as-float, weird row shape `['s' => …]` | **Adapter fits** — `SalesReportRepository::totalInPence(year, month)` hides all three |

The rule of thumb: **Adapter earns its keep when there is real translation
work to do** — vocabulary, units, shapes, return-type quirks, error
conventions. When the foreign API already speaks your language, you do
not need a translator.

---

## Exercise 1 — Email service adapter

### Before

```php
final class WelcomeEmailer
{
    public function send(User $user): void
    {
        $client = new ThirdPartyEmailClient(getenv('EMAIL_KEY'));
        $result = $client->send_message([
            'to_addr' => $user->email, 'from_addr' => 'noreply@example.com',
            'subj' => 'Welcome', 'msg_html' => '<p>Welcome aboard</p>',
            'tracking_id' => uniqid('email_'),
        ]);
        if ($result['status_code'] !== 'OK') throw new RuntimeException('Failed: ' . $result['err_text']);
    }
}
```

### After

```php
interface Mailer
{
    public function send(string $to, string $from, string $subject, string $htmlBody): void;
}

final class ThirdPartyMailerAdapter implements Mailer { /* translates inwards to send_message(…) */ }

final class WelcomeEmailer
{
    public function __construct(private Mailer $mailer) {}
    public function send(User $user): void
    {
        $this->mailer->send($user->email, 'noreply@example.com', 'Welcome', '<p>Welcome aboard</p>');
    }
}
```

### What the refactor buys

- **Domain vocabulary at the call site.** `send($to, $from, $subject, $htmlBody)`
  is what the use case wants to say.
- **The SDK's quirks live in one file.** `to_addr`, `subj`, `msg_html`,
  the tracking id, the `status_code === 'OK'` check, and the failure
  translation are *all* inside `ThirdPartyMailerAdapter`.
- **Two independent test surfaces.** The adapter is tested against the
  real SDK (right keys, right error mapping). The caller is tested
  against a `RecordingMailer` that records `(to, from, subject, body)`.
- **Swap-friendly.** Replacing the SDK is a new adapter class; nothing
  else changes.

---

## Exercise 2 — Geocoding (the trap)

### Before

```php
final class AddressValidator
{
    public function isValid(string $line): bool
    {
        $api = new ModernGeocoder(getenv('GEO_KEY'));
        return $api->geocode($line)->isValid();
    }
}
```

### Verdict — Adapter is the wrong answer

The SDK already speaks our language. `geocode($line)->isValid()` IS
`isValid($line): bool` with one extra hop. There is no translation —
no parameter renames, no unit conversion, no error-shape mismatch.

The actual smell is the hidden dependency (`new ModernGeocoder(getenv(…))`).
The fix is **plain DI through a thin domain interface**:

```php
interface AddressGeocoder { public function isValid(string $addressLine): bool; }
final class ModernGeocoder implements AddressGeocoder { /* ... */ }
final class AddressValidator
{
    public function __construct(private AddressGeocoder $geocoder) {}
    public function isValid(string $line): bool { return $this->geocoder->isValid($line); }
}
```

When does this become an Adapter? When the SDK's interface drifts from
ours, or when a second provider with a different shape arrives. The
domain interface is already there to receive the adapter on that day.
Until then, we would be writing a class that translates A to A.

---

## Exercise 3 — Legacy reporting database

### Before

```php
final class MonthlyReport
{
    public function totalSalesInPence(int $year, int $month): int
    {
        $legacy = new LegacyReportingDb();
        $rows = $legacy->raw_query("SELECT SUM(amount_dollars) AS s FROM old_sales WHERE yr = {$year} AND mo = {$month}");
        return (int) round(($rows[0]['s'] ?? 0) * 100);
    }
}
```

### After

```php
interface SalesReportRepository { public function totalInPence(int $year, int $month): int; }

final class LegacySalesReportAdapter implements SalesReportRepository { /* SQL, dollars→pence, ['s'=>…] */ }

final class MonthlyReport
{
    public function __construct(private SalesReportRepository $repository) {}
    public function totalSalesInPence(int $year, int $month): int { return $this->repository->totalInPence($year, $month); }
}
```

### What the refactor buys

- **All three foreign concerns live in one file.** The raw SQL, the
  dollars-to-pence conversion, and the legacy row shape (`['s' => …]`)
  are now inside `LegacySalesReportAdapter` and nowhere else.
- **The use case talks money in the project's units (pence) and asks
  for a year and a month.** No SQL, no dollars, no SQL injection
  surface, no `?? 0` defensive code.
- **Testable without the legacy DB.** The use case is exercised
  against an in-memory `SalesReportRepository` in seven lines of
  test setup.

---

## Chapter rubric

For each non-trap exercise:

- domain-shaped interface named in your terms (parameter names, units, types)
- adapter implementing that interface and translating inwards
- caller depending on the interface, with no foreign vocabulary visible
- focused tests for the adapter (correct translation) **and** for the caller (uses the interface)

For the trap: explain why DI through a thin domain interface is enough
when the foreign API already fits.

---

## How to run

```bash
cd php-design-patterns/adapter-chapter-4-guided-practice
php exercise-1-email-service-adapter/solution.php
php exercise-2-geocoding/solution.php
php exercise-3-legacy-reporting-database/solution.php
```
