# Chapter 5 guided practice — data shaping with arrays and simple value objects

Three exercises that practise the chapter's central question: when does an
associative array stop pulling its weight, and what should replace it?

- **Exercise 1** — recognise the smells (fields travelling together, derived
  values computed inline, the same shape built in three places) and promote
  the array to a real `Address` value object with named factories and
  enforced invariants.
- **Exercise 2** — design a brand-new `DateRange` value object from scratch,
  with an honest constructor, a half-open semantic, immutable modifiers and
  useful factory methods.
- **Exercise 3** — push the array → object conversion to a single named
  boundary so the inner service speaks only in typed objects.

Each solution preserves the observable behaviour of its starter (the driver
scripts produce identical output) — these are pure restructures.

## Exercise 1 — promote an array that has clearly outgrown the role

`Address` is currently an associative array. Three signals from the chapter
fire on it: the same fields travel together everywhere, two consumers compute
derived values inline, and the shape is built in three places with subtle
differences. Promote it to a value object.

### Smells in the starter

- **Primitive obsession.** `['line1' => …, 'postcode' => …, 'country' => …]`
  is passed around as a bag of strings; the type system tells you nothing.
- **Duplicated derived logic.** Both `summariseDelivery()` and
  `shippingRateFor()` reach into `country` to make decisions; the
  "(signature required)" rule lives only inside the formatter.
- **Three subtly different builders.** `$fromOrder`, `$fromCustomer` and
  `$fromForm` build the same shape three different ways — note that only
  the form site uppercases the postcode. That asymmetry is invisible until
  it bites.
- **No invariants.** Nothing stops a caller passing `['line1' => '']` or
  `['country' => 'XX']` and then watching downstream code misbehave.

### What the refactor buys

- A real `Address` value object that **enforces invariants in the
  constructor** (`line1` non-empty, `postcode` non-empty, `country` from a
  known list) — every consumer can now trust what it receives.
- The "build the address" sites become **named factory methods** —
  `Address::fromOrder()`, `Address::fromCustomer()`,
  `Address::fromFormRequest()` — so the postcode-uppercasing rule lives in
  exactly one place and can never drift between callers.
- Inline derived logic moves onto the object: `isUk()`,
  `requiresSignature()` and `summary()` are now properties of the address
  itself.
- `shippingRateFor()` deliberately **stays as a free function** that takes
  an `Address`. Weight is not a property of an address, so putting the
  rate logic on `Address` would mean the value object knew about the
  shipping domain — a leak in the other direction. The chapter calls this
  out: don't let the value object swallow logic that doesn't belong to it.

### Before

```php
function summariseDelivery(array $address): string
{
    $line = strtoupper(trim($address['line1'] ?? '')) . ', '
        . strtoupper(trim($address['postcode'] ?? ''));

    if (in_array($address['country'] ?? '', ['GB', 'IE'], true)) {
        $line .= ' (signature required)';
    }

    return $line;
}

function isUkAddress(array $address): bool
{
    return ($address['country'] ?? '') === 'GB';
}

function shippingRateFor(array $address, int $weightInGrams): int
{
    if (($address['country'] ?? '') === 'GB') {
        return $weightInGrams <= 1000 ? 360 : 750;
    }

    return 1200;
}

$fromOrder    = ['line1' => $order->ship_line1,       'postcode' => $order->ship_postcode,       'country' => $order->ship_country];
$fromCustomer = ['line1' => $customer->billing_line1, 'postcode' => $customer->billing_postcode,  'country' => $customer->billing_country];
$fromForm     = ['line1' => $request->input('line1'), 'postcode' => strtoupper($request->input('postcode')), 'country' => $request->input('country')];
```

### After

```php
final class Address
{
    private const KNOWN_COUNTRIES     = ['GB', 'IE', 'FR', 'DE', 'US'];
    private const SIGNATURE_COUNTRIES = ['GB', 'IE'];

    public function __construct(
        public readonly string $line1,
        public readonly string $postcode,
        public readonly string $country,
    ) {
        if (trim($line1) === '')    { throw new InvalidArgumentException('Address line1 must not be empty.'); }
        if (trim($postcode) === '') { throw new InvalidArgumentException('Address postcode must not be empty.'); }
        if (! in_array($country, self::KNOWN_COUNTRIES, true)) {
            throw new InvalidArgumentException("Unknown country code: {$country}");
        }
    }

    public static function fromOrder(StubOrderRow $order): self
    {
        return new self($order->ship_line1, $order->ship_postcode, $order->ship_country);
    }

    public static function fromCustomer(StubCustomerRow $customer): self
    {
        return new self($customer->billing_line1, $customer->billing_postcode, $customer->billing_country);
    }

    public static function fromFormRequest(StubFormRequest $request): self
    {
        return new self(
            $request->input('line1'),
            strtoupper($request->input('postcode')),
            $request->input('country'),
        );
    }

    public function isUk(): bool             { return $this->country === 'GB'; }
    public function requiresSignature(): bool { return in_array($this->country, self::SIGNATURE_COUNTRIES, true); }

    public function summary(): string
    {
        $line = strtoupper(trim($this->line1)) . ', ' . strtoupper(trim($this->postcode));

        if ($this->requiresSignature()) {
            $line .= ' (signature required)';
        }

        return $line;
    }
}

function shippingRateFor(Address $address, int $weightInGrams): int
{
    if ($address->isUk()) {
        return $weightInGrams <= 1000 ? 360 : 750;
    }

    return 1200;
}
```

## Exercise 2 — design a value object well, the first time

A fresh requirement: a `DateRange` that represents the half-open interval
`[startsOn, endsOn)`. Several services will use it, so the class itself is
the deliverable — designed honestly from the start rather than refactored
under pressure later.

### Design choices and rationale

- **`DateTimeImmutable` throughout.** Picking one and being consistent
  matters more than which one — `DateTimeImmutable` is part of the
  language, has no extra dependency, and its name advertises immutability.
- **Half-open `[startsOn, endsOn)`.** `contains($d)` is true when
  `startsOn <= $d < endsOn`. This makes `lengthInDays()` and adjacency
  ("does next month begin where this one ends?") trivially correct, and
  matches how billing periods, calendar months and shift slots behave in
  practice.
- **`lengthInDays()` for a single day is `1`, not `0`.** A range like
  `[Mon 00:00, Tue 00:00)` covers exactly one day; `0` would be a
  surprise. Documented at the top of the class so callers don't have to
  guess.
- **Constructor enforces `endsOn > startsOn`.** Empty or backwards ranges
  are not representable, so no consumer ever has to defensively check
  for them.
- **Immutable.** No setters; `extendedBy(int $days)` returns a *new*
  `DateRange` rather than mutating in place. Sharing instances between
  services is then safe by construction.
- **Useful factory methods.** `forSingleDay()` and `forCalendarMonth()`
  encode the two most common ways callers actually want to construct a
  range, so they don't have to compute month boundaries themselves.

### Before

(no legacy — this exercise is a greenfield design)

### After

```php
final class DateRange
{
    public function __construct(
        public readonly DateTimeImmutable $startsOn,
        public readonly DateTimeImmutable $endsOn,
    ) {
        if ($endsOn <= $startsOn) {
            throw new InvalidArgumentException('DateRange endsOn must be strictly after startsOn.');
        }
    }

    public static function forSingleDay(DateTimeImmutable $date): self
    {
        $start = $date->setTime(0, 0, 0);
        return new self($start, $start->modify('+1 day'));
    }

    public static function forCalendarMonth(DateTimeImmutable $anyDayInMonth): self
    {
        $start = $anyDayInMonth->modify('first day of this month')->setTime(0, 0, 0);
        return new self($start, $start->modify('+1 month'));
    }

    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->startsOn && $date < $this->endsOn;
    }

    public function lengthInDays(): int
    {
        return (int) $this->startsOn->diff($this->endsOn)->days;
    }

    public function overlapsWith(self $other): bool
    {
        return $this->startsOn < $other->endsOn
            && $other->startsOn < $this->endsOn;
    }

    public function extendedBy(int $days): self
    {
        if ($days < 0) {
            throw new InvalidArgumentException('extendedBy() expects a non-negative number of days.');
        }

        return new self($this->startsOn, $this->endsOn->modify("+{$days} day"));
    }
}
```

## Exercise 3 — push array conversions to a single boundary

The starter converts arrays to objects *inside* the service: `OrderService`
takes an `array $payload`, validates it with `isset()` calls, and builds
typed objects on the fly. Push the conversion out to the controller's
edge so the service only ever speaks in typed objects.

### Smells in the starter

- **Validation in the wrong place.** `OrderService` mixes domain logic
  ("draft an order, add lines, save it") with HTTP-shaped validation
  ("is `customer_id` an int? are `items` an array?"). Two responsibilities
  in one method.
- **`array` parameters with hidden invariants.** `placeOrder(array $payload)`
  tells callers nothing about what shape they need to pass; you have to
  read the body to find out.
- **Conversion smeared across the service.** `new CustomerId(...)`,
  `new ProductId(...)`, `new OrderLine(...)` happen mid-loop, interleaved
  with validation — there is no single named place that says
  "this is where untyped data becomes typed".

### What the refactor buys

- A small **boundary layer** — `OrderRequest::fromHttpRequest()` and
  `OrderLineRequest::fromArray()` — that is the **only** place arrays
  appear. Everything past that point is typed.
- `OrderService::placeOrder()` now takes an `OrderRequest` and contains
  zero `isset()`, zero `is_int()`, zero array indexing. Its body shrinks
  to the actual domain operation: draft the order, add the lines, save.
- The exception messages move with the conversion (they are about HTTP
  shape, not about the domain), so when a future caller — a queue worker,
  say — wants to place an order from already-typed data, it can build an
  `OrderRequest` directly and skip HTTP validation entirely.
- Easier to test: you can construct an `OrderRequest` in a test without
  going through `Request`/JSON at all.

### Before

```php
final class OrderController
{
    public function __construct(private OrderService $service) {}

    public function store(Request $request): JsonResponse
    {
        $payload = $request->json()->all();
        $order   = $this->service->placeOrder($payload);

        return response()->json(['id' => $order->id, 'reference' => $order->reference]);
    }
}

final class OrderService
{
    public function placeOrder(array $payload): Order
    {
        if (! isset($payload['customer_id']) || ! is_int($payload['customer_id'])) {
            throw new InvalidArgumentException('customer_id required');
        }
        if (! isset($payload['items']) || ! is_array($payload['items']) || $payload['items'] === []) {
            throw new InvalidArgumentException('items required');
        }

        $order = Order::draft(new CustomerId($payload['customer_id']));

        foreach ($payload['items'] as $item) {
            if (! isset($item['product_id'], $item['quantity'])) {
                throw new InvalidArgumentException('item missing fields');
            }

            $order->addLine(new OrderLine(
                productId: new ProductId((int) $item['product_id']),
                quantity:  (int) $item['quantity'],
            ));
        }

        $this->repository->save($order);

        return $order;
    }
}
```

### After

```php
final class OrderLineRequest
{
    public function __construct(
        public readonly ProductId $productId,
        public readonly int       $quantity,
    ) {}

    public static function fromArray(array $item): self
    {
        if (! isset($item['product_id'], $item['quantity'])) {
            throw new InvalidArgumentException('item missing fields');
        }

        return new self(new ProductId((int) $item['product_id']), (int) $item['quantity']);
    }
}

final class OrderRequest
{
    /** @param list<OrderLineRequest> $lines */
    public function __construct(
        public readonly CustomerId $customerId,
        public readonly array      $lines,
    ) {
        if ($lines === []) {
            throw new InvalidArgumentException('items required');
        }
    }

    public static function fromHttpRequest(Request $request): self
    {
        $payload = $request->json()->all();

        if (! isset($payload['customer_id']) || ! is_int($payload['customer_id'])) {
            throw new InvalidArgumentException('customer_id required');
        }
        if (! isset($payload['items']) || ! is_array($payload['items']) || $payload['items'] === []) {
            throw new InvalidArgumentException('items required');
        }

        $lines = array_map(
            static fn (array $item): OrderLineRequest => OrderLineRequest::fromArray($item),
            $payload['items'],
        );

        return new self(new CustomerId($payload['customer_id']), $lines);
    }
}

final class OrderController
{
    public function __construct(private OrderService $service) {}

    public function store(Request $request): JsonResponse
    {
        $order = $this->service->placeOrder(OrderRequest::fromHttpRequest($request));

        return response()->json(['id' => $order->id, 'reference' => $order->reference]);
    }
}

final class OrderService
{
    public function __construct(private InMemoryOrderRepository $repository) {}

    public function placeOrder(OrderRequest $request): Order
    {
        $order = Order::draft($request->customerId);

        foreach ($request->lines as $line) {
            $order->addLine(new OrderLine(
                productId: $line->productId,
                quantity:  $line->quantity,
            ));
        }

        $this->repository->save($order);

        return $order;
    }
}
```

## Running the solutions

Each exercise folder is self-contained and can be executed with plain PHP
(no Composer, no framework):

```bash
cd writing-decent-php/data-shaping-chapter-5-guided-practice/exercise-1-promote-array-to-value-object
php starter.php
php solution.php
diff <(php starter.php) <(php solution.php)   # no output ⇒ behaviour preserved
```

Repeat for exercises 2 and 3. Exercise 2 has no starter — its `solution.php`
prints a short demo that exercises the constructor invariant, the half-open
`contains()` semantic, the factory methods and the immutable `extendedBy()`.
