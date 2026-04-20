# Chapter 3 guided practice — functions that do one job

Reference solutions for the three structural refactors in **Functions that do
one job → Chapter 3 guided practice**
(`/learn/sections/chapter-functions-that-do-one-job/chapter-3-guided-practice`).

Every refactor is a pure restructure: same input → same return values → same
side effects. The shape of the code changes; what it does does not.

## Exercises

| # | Exercise                                          | Starter | Solution |
| - | ------------------------------------------------- | ------- | -------- |
| 1 | Split a function that does too much               | [`exercise-1-.../starter.php`](exercise-1-split-a-function-that-does-too-much/starter.php) | [`exercise-1-.../solution.php`](exercise-1-split-a-function-that-does-too-much/solution.php) |
| 2 | Separate a query from a command                   | [`exercise-2-.../starter.php`](exercise-2-separate-a-query-from-a-command/starter.php) | [`exercise-2-.../solution.php`](exercise-2-separate-a-query-from-a-command/solution.php) |
| 3 | Remove flag arguments and reshape the parameters  | [`exercise-3-.../starter.php`](exercise-3-remove-flag-arguments/starter.php) | [`exercise-3-.../solution.php`](exercise-3-remove-flag-arguments/solution.php) |

## Exercise 1 — split a function that does too much

**Brief:** `processFeedbackSubmission()` validates input, writes to the
database, mails the team, mails the submitter, and writes log entries — all in
one body, with `$db`, `$mailer`, and `$logger` reached for via `global`.
Refactor into focused pieces. Keep the same return shape and the same side
effects.

**Smells in the starter:**

- Hidden dependencies pulled in via `global`.
- Section comments (`// ----- validate -----`, `// ----- store -----`) — a
  giveaway that the function is doing several different jobs.
- One body that mixes input validation, persistence, two notification
  channels, and observability.
- The function's name needs three "ands" to describe it honestly
  (*validate-store-and-notify*).

**Before:**

```php
function processFeedbackSubmission(array $input): array
{
    global $db, $mailer, $logger;

    // ----- validate -----
    $errors = [];
    if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'invalid_email';
    }
    if (empty($input['message']) || strlen($input['message']) < 10) {
        $errors[] = 'message_too_short';
    }
    if ($errors !== []) {
        $logger->warning('feedback_invalid', ['input' => $input, 'errors' => $errors]);
        return ['ok' => false, 'errors' => $errors];
    }

    // ----- store -----
    $id = $db->insert('feedback', [
        'email'        => $input['email'],
        'message'      => $input['message'],
        'submitted_at' => time(),
    ]);

    // ----- notify team -----
    $mailer->send('team@example.com', 'New feedback', "From {$input['email']}: {$input['message']}");

    // ----- thank user -----
    $mailer->send($input['email'], 'Thanks for the feedback', 'We received your message.');

    $logger->info('feedback_recorded', ['id' => $id]);

    return ['ok' => true, 'id' => $id];
}
```

**After:**

```php
final class FeedbackSubmissionHandler
{
    private const MIN_MESSAGE_LENGTH = 10;

    public function __construct(
        private InMemoryDb       $database,
        private RecordingMailer  $mailer,
        private RecordingLogger  $logger,
        private string           $teamRecipient = 'team@example.com',
    ) {
    }

    public function handle(array $input): array
    {
        $validationErrors = $this->validate($input);
        if ($validationErrors !== []) {
            $this->logger->warning('feedback_invalid', ['input' => $input, 'errors' => $validationErrors]);

            return ['ok' => false, 'errors' => $validationErrors];
        }

        $feedbackId = $this->store($input);

        $this->notifyTeam($input);
        $this->thankSubmitter($input['email']);

        $this->logger->info('feedback_recorded', ['id' => $feedbackId]);

        return ['ok' => true, 'id' => $feedbackId];
    }

    private function validate(array $input): array { /* ... */ }
    private function store(array $input): int      { /* ... */ }
    private function notifyTeam(array $input): void { /* ... */ }
    private function thankSubmitter(string $submitterEmail): void { /* ... */ }
}
```

What the refactor buys:

- **Honest signature.** The constructor lists every collaborator the handler
  needs. No more `global`, no more surprises in test setup.
- **`handle()` reads top-to-bottom as a story:** validate, store, notify, log.
  The four section comments became four method names — the names *are* the
  documentation.
- **Each private method has one job and one reason to change.** Tweaking the
  team email subject does not pull validation into the diff.
- **Return shape is preserved**, so every existing caller keeps working.

Run it: `php exercise-1-split-a-function-that-does-too-much/solution.php` —
the output, the number of mails sent, and the number of log entries written
are identical to the starter.

## Exercise 2 — separate a query from a command

**Brief:** `ApiKeyService::getApiKey()` and `isApiKeyValid()` both pretend to
be queries while quietly mutating counters. Split them so each method either
returns information *or* changes state, but never both.

**Smells in the starter:**

- A `get*` method writes (`incrementUsageCount`, `updateLastUsedAt`) — callers
  cannot read without causing side effects.
- A predicate (`isApiKeyValid`) writes (`incrementValidationCount`) — calling
  the same method twice with the same key produces different observable
  state.
- A diagnostic call from a developer's tinker session would silently corrupt
  metrics.

**Before:**

```php
final class ApiKeyService
{
    public function __construct(private ApiKeyRepository $repository) {}

    public function getApiKey(int $keyId): array
    {
        $key = $this->repository->findById($keyId);
        $this->repository->incrementUsageCount($keyId);
        $this->repository->updateLastUsedAt($keyId, time());

        return $key;
    }

    public function isApiKeyValid(int $keyId): bool
    {
        $key = $this->repository->findById($keyId);
        if ($key === null) {
            return false;
        }
        $this->repository->incrementValidationCount($keyId);

        return $key['expires_at'] > time();
    }
}
```

**After:**

```php
final class ApiKeyReader
{
    public function __construct(private InMemoryApiKeyRepository $repository) {}

    public function findApiKey(int $keyId): ?array
    {
        return $this->repository->findById($keyId);
    }

    public function isApiKeyValid(int $keyId): bool
    {
        $key = $this->repository->findById($keyId);

        if ($key === null) {
            return false;
        }

        return $key['expires_at'] > time();
    }
}

final class ApiKeyUsageRecorder
{
    public function __construct(private InMemoryApiKeyRepository $repository) {}

    public function recordUsage(int $keyId): void
    {
        $this->repository->incrementUsageCount($keyId);
        $this->repository->updateLastUsedAt($keyId, time());
    }

    public function recordValidationAttempt(int $keyId): void
    {
        $this->repository->incrementValidationCount($keyId);
    }
}
```

A caller that wants to read *and* bump the counter now expresses both
intentions explicitly:

```php
$apiKey = $reader->findApiKey($keyId);
$recorder->recordUsage($keyId);
```

What the refactor buys:

- **Pure queries are cheap to call.** A diagnostic, a cache check, or a unit
  test can read freely without poisoning metrics.
- **Side effects are visible at the call site.** When you see
  `$recorder->recordUsage(...)`, you know exactly when usage moves.
- **Each class has one reason to change.** The reader changes when read rules
  change; the recorder changes when usage tracking changes.

Run it: `php exercise-2-separate-a-query-from-a-command/solution.php` — both
the returned key and the final counters match the starter exactly.

## Exercise 3 — remove flag arguments and reshape the parameters

**Brief:** `generateInvoicePdf()` takes eight positional parameters, including
four booleans. Refactor so the call sites read themselves and the four sample
calls keep producing equivalent output.

**Smells in the starter:**

- Eight positional parameters — call sites are a wall of unlabelled
  arguments.
- Four boolean rendering flags clustered together — the classic "options
  object" smell.
- `bool $watermarkAsDraft` flips the function's *mode* — a sign that it
  should be two methods, not one.
- `bool $emailToCustomer` is a side effect tacked onto a generator — a
  separate concern wearing a flag.

**Before:**

```php
function generateInvoicePdf(
    int    $invoiceId,
    string $template,
    bool   $includeLogo,
    bool   $includeQrCode,
    bool   $includeBankDetails,
    bool   $watermarkAsDraft,
    string $locale          = 'en-GB',
    bool   $emailToCustomer = false,
): string {
    // ... builds and returns PDF bytes ...
}

generateInvoicePdf(42, 'standard', true, true,  true,  false);
generateInvoicePdf(42, 'standard', true, false, false, true);
generateInvoicePdf(99, 'minimal',  false, false, false, false, 'fr-FR');
generateInvoicePdf(99, 'minimal',  true,  true,  true,  false, 'en-GB', true);
```

**After:**

```php
final class InvoicePdfOptions
{
    public function __construct(
        public readonly bool $includeLogo        = false,
        public readonly bool $includeQrCode      = false,
        public readonly bool $includeBankDetails = false,
    ) {
    }

    public static function none(): self     { return new self(); }
    public static function logoOnly(): self { return new self(includeLogo: true); }
    public static function full(): self     { return new self(includeLogo: true, includeQrCode: true, includeBankDetails: true); }
}

final class InvoicePdfRenderer
{
    public function generateFinalInvoicePdf(int $invoiceId, string $template, InvoicePdfOptions $options, string $locale = 'en-GB'): string { /* ... */ }
    public function generateDraftInvoicePdf(int $invoiceId, string $template, InvoicePdfOptions $options, string $locale = 'en-GB'): string { /* ... */ }
}

final class InvoiceMailer
{
    public function emailInvoiceToCustomer(int $invoiceId, string $pdfBytes): void { /* ... */ }
}
```

The four call sites now read like English:

```php
$renderer->generateFinalInvoicePdf(42, 'standard', InvoicePdfOptions::full());
$renderer->generateDraftInvoicePdf(42, 'standard', InvoicePdfOptions::logoOnly());
$renderer->generateFinalInvoicePdf(99, 'minimal',  InvoicePdfOptions::none(), 'fr-FR');

$pdfBytes = $renderer->generateFinalInvoicePdf(99, 'minimal', InvoicePdfOptions::full());
$mailer->emailInvoiceToCustomer(99, $pdfBytes);
```

What the refactor buys:

- **The "rendering options" cluster becomes one named value object.** Adding
  a fifth flag later is one extra named property, not a sixth boolean
  positional argument.
- **`watermarkAsDraft` is gone.** Two intent-named methods —
  `generateDraftInvoicePdf` and `generateFinalInvoicePdf` — make the call
  site say *which kind* of invoice you wanted instead of decoding `false`.
- **Emailing is a separate concern.** `InvoiceMailer::emailInvoiceToCustomer()`
  takes the bytes and sends them. Generating the PDF no longer has a hidden
  outbound-email side effect, and a caller who only wants the bytes stays
  uncoupled from the mail system.
- **The `'en-GB'` and `false` defaults that meant "the normal case" are gone**
  — the normal case is now spelled out by the method name and the
  `InvoicePdfOptions` factory.

Run it: `php exercise-3-remove-flag-arguments/solution.php` — the first three
PDFs match byte-for-byte. The fourth's body matches too; the email is now an
explicit `InvoiceMailer` call rather than a smuggled flag, which is exactly
the change the brief asks for.

## How to run all three

From this folder:

```bash
php exercise-1-split-a-function-that-does-too-much/solution.php
php exercise-2-separate-a-query-from-a-command/solution.php
php exercise-3-remove-flag-arguments/solution.php
```

Each script prints the same observable result as its starter — proof the
refactors changed the shape of the code without changing what it does.

← [Writing decent PHP](../README.md)
