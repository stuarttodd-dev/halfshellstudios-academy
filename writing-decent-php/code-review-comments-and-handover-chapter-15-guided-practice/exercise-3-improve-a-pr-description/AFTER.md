# The rewritten PR

## Title

```
Make order creation idempotent via client-supplied Idempotency-Key header
```

Rules applied:

- Imperative mood, reads as a sentence starting with "This PR will...".
- Names the *behaviour* that changes (order creation is now idempotent)
  and the *mechanism* (Idempotency-Key header). A reader scanning the
  git log six months later can decide whether this is the PR they are
  looking for, without opening it.
- Fits in ~80 characters.

---

## Description

```markdown
## Why
Duplicate order creation is currently possible whenever the client retries
POST /orders — e.g. because the user double-taps "Pay", the mobile app
loses connectivity mid-request, or our own HTTP client times out. INC-412
documents three duplicate-charge incidents in the last month from this
exact path.

## What
- Accepts an `Idempotency-Key` header on POST /orders (UUIDv4 required).
- Persists `(idempotency_key, response_body, response_status)` per-tenant
  on first success.
- On retry with the same key: returns the original stored response
  verbatim, without re-entering the order-creation use case or hitting
  the payment gateway again.
- Rejects a retry whose *request body* differs from the original with
  HTTP 409 and a machine-readable `code: idempotency_key_mismatch`.

## How
- Added `OrderIdempotencyStore` (port) with `PdoOrderIdempotencyStore`
  adapter — schema migration in db/migrations/2026_04_21_001_create_order_idempotency_keys.php.
- `PlaceOrderController` consults the store before and after the use
  case. The use case itself is unchanged and still knows nothing about
  HTTP concerns (see the Chapter 6 boundaries pattern).
- Keys expire after 24h; cleanup is a daily scheduled job.

## Testing
- 4 new unit tests for `PdoOrderIdempotencyStore` (save-once, read-back,
  body-mismatch, expiry window).
- 3 new feature tests hitting POST /orders end-to-end:
  - happy path (first call stores, second call replays)
  - body-mismatch on retry returns 409
  - missing header → behaviour unchanged (backwards compatible)
- Manual verification on staging against the sandbox Stripe account
  documented at https://internal.wiki/checkout-idempotency-manual-test.

## Risk
- Getting idempotency wrong on the checkout path is how you end up
  double-charging a customer. Mitigations:
    - The store is append-only and writes happen inside the same DB
      transaction as the order insert, so partial state is impossible.
    - `PlaceOrderController` now short-circuits BEFORE it touches the
      payment gateway, so a retry can never issue a second charge.
    - Rollback plan is flag-gated (`features.order_idempotency`, off by
      default in prod) so we can disable without a redeploy.

## Out of scope
- Idempotency on POST /refunds and POST /invoices — same pattern, follow-up PR.
- Rate limiting. Separate concern, tracked in issue-461.

## Links
- Incident: INC-412
- Design doc: docs/design/order-idempotency.md
- ADR: ADR-019 (client-supplied idempotency keys vs server-generated)
- Ticket: CHECK-812
```

Rules applied:

- **Why** before **What**. The reviewer needs to understand the
  motivating problem before they look at code.
- **How** gives the reviewer the *shape* of the change, so they can
  check the diff against a mental model instead of reverse-engineering
  it.
- **Testing** says what the reviewer can assume was exercised.
- **Risk** says out loud what could go wrong, and the concrete
  mitigation for each risk. This is the single most under-used section
  in real PRs.
- **Out of scope** prevents "why didn't you also fix X?" reviews.
- **Links** point at incident, ticket, design doc, and ADR — the four
  artefacts a future archaeologist would look for.

---

## Commit message

```
Make order creation idempotent via client-supplied Idempotency-Key

Wrap POST /orders in an idempotency layer that stores the first
successful response keyed by (tenant_id, idempotency_key) and replays
it verbatim on retry. Mismatched retries are rejected with HTTP 409
before the order use case runs, so a second Stripe charge is impossible.

The feature is gated behind `features.order_idempotency` (off by
default in prod) so we can roll back without a deploy. See ADR-019
for the decision to accept client-supplied keys rather than generating
them server-side.

Closes CHECK-812. Mitigates INC-412.
```

Rules applied:

- **Subject line ≤ ~72 characters, imperative mood.** A reader skimming
  `git log --oneline` learns what this commit *does*, not what it
  *feels like*.
- **Blank line, then the body.** Wrapped at ~72 columns so `git log`
  and `git show` read well in a terminal.
- **The body covers the change in one paragraph, the operational note
  (feature flag / rollback story) in the next, and the cross-references
  (ADR, ticket, incident) in the last.**
- **No "fix bug".** The word "fix" in a commit message is a code smell
  on its own — it presumes the reader already knows what was broken.
