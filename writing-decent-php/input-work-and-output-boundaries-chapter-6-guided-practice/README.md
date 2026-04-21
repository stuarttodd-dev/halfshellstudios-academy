# Chapter 6 guided practice — separating input, work, and output

Three exercises that put the chapter's central model — **input → work →
output** — into practice. Each refactor tightens one of the three
boundaries:

- **Exercise 1** — pull the *input* shape and validation out of the
  controller into a typed `RescheduleBookingInput`, leaving the
  controller as a thin parse → call → respond stub.
- **Exercise 2** — pull HTTP out of the *work*: the use case should
  speak in domain types only, with the boundary translation happening
  outside it.
- **Exercise 3** — pull the *output* shaping out of the use case: it
  returns a typed value, and a thin presenter formats that value into
  whatever wire format the edge needs.

Every solution is a pure restructure — running starter and solution
produces identical output (the drivers in each exercise verify this).

## Exercise 1 — extract the input

The controller is doing four jobs: pulling fields off the request,
validating them, performing the database update, and building the
response. Move the first two into a typed `RescheduleBookingInput` so the
controller body shrinks to "parse → call → respond".

### Smells in the starter

- **Validation interleaved with work.** Each invalid case `return`s a
  `JsonResponse` from inside the controller; the happy path is harder
  to spot.
- **Untyped values flowing forward.** `$newDate` and `$reason` are
  plain strings the moment they leave the request, then travel to the
  DB call without ever being wrapped in something the type system can
  reason about.
- **No place to test validation in isolation.** To assert "an empty date
  is rejected" you have to construct a `Request` and invoke the
  controller — there is no smaller seam.
- **Magic number in the body.** `500` for "max reason length" lives
  inline, with no name.

### What the refactor buys

- A **`RescheduleBookingInput` value object** whose constructor enforces
  every invariant (`new_date` matches `YYYY-MM-DD`, `reason` ≤ 500
  chars). Anywhere that holds one is guaranteed to hold a valid one.
- A **named factory** — `RescheduleBookingInput::fromRequest($request,
  $bookingId)` — that is the only place HTTP shape leaks in. Tests can
  build the input directly from constructor args; the controller test
  exercises the parsing.
- A **`RescheduleBooking` use case** that takes the typed input and
  performs the DB update — no HTTP, no `Request`, no validation.
- A **thin controller** of eight lines: try the parse, dispatch the use
  case, return success — exactly one job per line.
- The `500` becomes a named `MAX_REASON_LENGTH` constant on the input
  class.

### Before

```php
final class RescheduleBookingController
{
    public function __invoke(Request $request, int $bookingId): JsonResponse
    {
        $newDate = $request->input('new_date');
        if (! $newDate || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
            return new JsonResponse(['error' => 'new_date must be YYYY-MM-DD'], 422);
        }

        $reason = trim($request->input('reason', ''));
        if (strlen($reason) > 500) {
            return new JsonResponse(['error' => 'reason too long'], 422);
        }

        DB::table('bookings')->where('id', $bookingId)->update([
            'starts_on'         => $newDate,
            'reschedule_reason' => $reason,
        ]);

        return new JsonResponse(['status' => 'rescheduled']);
    }
}
```

### After

```php
final class RescheduleBookingInput
{
    private const MAX_REASON_LENGTH = 500;

    public function __construct(
        public readonly int    $bookingId,
        public readonly string $newDate,
        public readonly string $reason,
    ) {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
            throw new InvalidInputException('new_date must be YYYY-MM-DD');
        }

        if (strlen($reason) > self::MAX_REASON_LENGTH) {
            throw new InvalidInputException('reason too long');
        }
    }

    public static function fromRequest(Request $request, int $bookingId): self
    {
        return new self(
            bookingId: $bookingId,
            newDate:   (string) ($request->input('new_date') ?? ''),
            reason:    trim((string) $request->input('reason', '')),
        );
    }
}

final class InvalidInputException extends RuntimeException {}

final class RescheduleBooking
{
    public function execute(RescheduleBookingInput $input): void
    {
        DB::table('bookings')->where('id', $input->bookingId)->update([
            'starts_on'         => $input->newDate,
            'reschedule_reason' => $input->reason,
        ]);
    }
}

final class RescheduleBookingController
{
    public function __construct(private RescheduleBooking $useCase) {}

    public function __invoke(Request $request, int $bookingId): JsonResponse
    {
        try {
            $input = RescheduleBookingInput::fromRequest($request, $bookingId);
        } catch (InvalidInputException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        $this->useCase->execute($input);

        return new JsonResponse(['status' => 'rescheduled']);
    }
}
```

## Exercise 2 — pull the rule out of the boundary

`CalculateRefund::calculate()` is "mostly business logic", but it takes a
`Request`. That single parameter ties the entire use case to HTTP — it
can't be reused from a queue worker, an admin script, or a test that
doesn't want to fake a `Request`.

### Smells in the starter

- **HTTP type leaking into a domain method.** The use case has to know
  the field name (`'reason'`), the source of truth for it (HTTP), and
  what to do when it's missing.
- **Stringly-typed switch.** `if ($reason === 'goodwill')` couples the
  business rule to a magic string — typos compile, unknown values
  silently fall through to the `0.8` branch.
- **Untyped return.** Returning a bare `int` works, but the unit
  ("pence") lives only in the variable name `$base`. The next caller
  has to read the body to find out.

### What the refactor buys

- A **`RefundReason` enum** — the use case now takes `(Order $order,
  RefundReason $reason)`. Unknown values can't reach it. The chapter's
  point: the *domain concept* (refund reason) moves into the domain;
  the *wire shape* (the string `'goodwill'` from a form) stays at the
  edge.
- A **`RefundAmount` value object** — a one-field wrapper over `int
  $pence`. Makes the unit explicit at every call site, gives the
  invariant ("non-negative") a place to live, and gives the codebase
  somewhere to grow (currency, breakdown, notes) without changing
  every caller.
- A **`CalculateRefundController`** at the boundary that translates the
  raw HTTP `reason` string into the enum (defaulting to
  `RefundReason::Standard` to preserve the original "fall through" for
  unknown values) and then dispatches.
- The use case is now **trivially unit-testable**: build an `Order`,
  pass an enum, assert the pence — no `Request`, no HTTP fakes, no
  global mocks.

### Before

```php
final class CalculateRefund
{
    public function calculate(Order $order, Request $request): int
    {
        $reason = $request->input('reason');
        $base   = $order->totalInPence;

        if ($reason === 'goodwill') {
            return $base;
        }
        if ($order->placedAt < new DateTimeImmutable('-30 days')) {
            return 0;
        }
        return (int) round($base * 0.8);
    }
}
```

### After

```php
enum RefundReason: string
{
    case Goodwill = 'goodwill';
    case Standard = 'standard';
}

final class RefundAmount
{
    public function __construct(public readonly int $pence)
    {
        if ($pence < 0) {
            throw new InvalidArgumentException('Refund amount cannot be negative.');
        }
    }

    public static function none(): self
    {
        return new self(0);
    }
}

final class CalculateRefund
{
    private const STANDARD_REFUND_WINDOW = '-30 days';
    private const STANDARD_REFUND_RATE   = 0.8;

    public function calculate(Order $order, RefundReason $reason): RefundAmount
    {
        if ($reason === RefundReason::Goodwill) {
            return new RefundAmount($order->totalInPence);
        }

        if ($order->placedAt < new DateTimeImmutable(self::STANDARD_REFUND_WINDOW)) {
            return RefundAmount::none();
        }

        return new RefundAmount((int) round($order->totalInPence * self::STANDARD_REFUND_RATE));
    }
}

final class CalculateRefundController
{
    public function __construct(private CalculateRefund $useCase) {}

    public function __invoke(Order $order, Request $request): RefundAmount
    {
        $rawReason = (string) ($request->input('reason') ?? '');
        $reason    = RefundReason::tryFrom($rawReason) ?? RefundReason::Standard;

        return $this->useCase->calculate($order, $reason);
    }
}
```

## Exercise 3 — separate output from work

`GenerateOrderSummary::run()` does two things: it fetches and projects
the data, and it formats that data as JSON. Pull the formatting out so
the use case returns a typed value, and a tiny presenter owns the wire
format.

### Smells in the starter

- **Use case returns a wire format.** Returning a `string` of JSON means
  any caller that wants the same data in CSV, HTML, or an email has to
  re-do the work — or, worse, parse the JSON back.
- **Formatting choices baked into the work.** The currency symbol
  (`£`), the reference prefix (`ORDER-`), the zero-pad width (`6`) and
  the decimal places all live inline. None of them are *work*; they're
  output decisions.
- **Hard to test what matters.** Asserting the use case "summarises an
  order" requires a string equality on a JSON blob, which couples the
  test to the formatter.

### What the refactor buys

- An **`OrderSummary` value object** that carries raw domain values
  (`orderId`, `totalInPence`, `itemCount`) — no formatting, no JSON.
  Tests on the use case can assert each field by name.
- A **`GenerateOrderSummary` use case** of three lines: fetch, project,
  return — pure work.
- An **`OrderSummaryJsonPresenter`** that owns the entire wire format
  — reference prefix, pad length, currency symbol, and `json_encode`.
  Swap this for `OrderSummaryHtmlPresenter` or
  `OrderSummaryCsvPresenter` without touching the use case.
- The formatting magic numbers become **named constants on the
  presenter**, where they actually belong.

### Before

```php
final class GenerateOrderSummary
{
    public function run(int $orderId): string
    {
        $order = Order::find($orderId);
        return json_encode([
            'reference' => 'ORDER-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'total'     => '£' . number_format($order->totalInPence / 100, 2),
            'items'     => $order->items->count(),
        ]);
    }
}
```

### After

```php
final class OrderSummary
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $totalInPence,
        public readonly int $itemCount,
    ) {}
}

final class GenerateOrderSummary
{
    public function run(int $orderId): OrderSummary
    {
        $order = Order::find($orderId);

        return new OrderSummary(
            orderId:      $order->id,
            totalInPence: $order->totalInPence,
            itemCount:    $order->items->count(),
        );
    }
}

final class OrderSummaryJsonPresenter
{
    private const REFERENCE_PREFIX     = 'ORDER-';
    private const REFERENCE_PAD_LENGTH = 6;
    private const CURRENCY_SYMBOL      = '£';

    public function present(OrderSummary $summary): string
    {
        return json_encode([
            'reference' => self::REFERENCE_PREFIX
                . str_pad((string) $summary->orderId, self::REFERENCE_PAD_LENGTH, '0', STR_PAD_LEFT),
            'total'     => self::CURRENCY_SYMBOL . number_format($summary->totalInPence / 100, 2),
            'items'     => $summary->itemCount,
        ]);
    }
}
```

## Running the solutions

Each exercise folder is self-contained and can be executed with plain
PHP — no Composer, no framework, no database:

```bash
cd writing-decent-php/input-work-and-output-boundaries-chapter-6-guided-practice/exercise-1-extract-the-input
php starter.php
php solution.php
diff <(php starter.php) <(php solution.php)   # no output ⇒ behaviour preserved
```

Repeat for exercises 2 and 3. Each driver runs every interesting case
(happy path, missing field, edge values, etc.) so the diff is a real
check that the refactor didn't change observable behaviour.
