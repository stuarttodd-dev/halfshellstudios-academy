# ADR-001 — Stripe over PayPal for card payments

## Status
Accepted (2026-04)

## Context
The checkout flow needs to accept credit and debit cards for UK and EU
customers paying in GBP and EUR, starting Q3. Legal and finance have
already approved both Stripe and PayPal as processors; the engineering
decision is which one the domain code should couple to.

Non-negotiables on our side:

- 3-D Secure 2 (PSD2 SCA) out of the box, without custom callbacks.
- A sandbox a CI job can hit with no manual clicks and no shared tenant.
- Idempotent `charge` and `refund` endpoints that accept a client-provided
  key, so we can safely retry on our side.
- A PHP SDK with typed responses (we do not want to hand-roll JSON shapes).
- Webhooks we can sign and verify locally, so end-to-end tests do not
  need a public URL.

We have roughly 60 hours of engineering time budgeted for payments in
this quarter, so "cheaper to integrate" weighs as much as "cheaper per
transaction".

## Decision
Use **Stripe** as the card processor for checkout. The domain code
depends on a `PaymentGateway` port; the only adapter that ships in this
quarter is `StripePaymentGateway`. `StripeClient` is instantiated
exclusively in the composition root (see ADR-007 on dependency
injection).

PayPal (via Braintree) stays on the table as a future adapter for
customers who specifically request it, but we will not wire it in now.

## Consequences

**Positive**

- First card charge goes out in an estimated 10 hours of engineering
  time, leaving budget for the refund and webhook flows.
- 3-D Secure 2, SCA, Apple Pay, and Google Pay are all handled by
  Stripe's hosted elements with no bespoke JS on our side.
- The Stripe PHP SDK returns typed objects, so the adapter is the
  only code that deals in SDK shapes — downstream code sees
  `PaymentGateway`.
- CI can exercise the full happy path against the Stripe test mode
  with a fixed set of deterministic test cards.

**Negative / limits**

- We are now coupled to Stripe's fee structure (≈1.5% + 20p per UK
  transaction at our current volume). If volume grows past
  ~£2m/month, we will need to revisit pricing.
- Adding a second processor later costs us whatever effort is needed
  to build a second adapter — **but** because the domain depends on
  `PaymentGateway`, that cost is isolated to one adapter class plus
  its tests, not a checkout rewrite.
- If Stripe has a regional outage, we have no fallback processor. We
  accept this for the first release; a fallback is tracked in
  [issue-123].

**Neutral**

- The `idempotency_key` column on `orders` is needed regardless of
  processor; no rework if we add PayPal later.

## Alternatives considered

- **PayPal (via Braintree)** — rejected as the *default* because the
  UK integration still routes some 3-D Secure 2 challenges through a
  redirect, and the Braintree PHP SDK ships fewer typed objects than
  Stripe's. Revisit if a significant fraction of customers pay via
  PayPal balance (not cards) — that is a PayPal-specific feature
  Stripe cannot match.
- **Adyen** — rejected for Q3 on integration cost only. Strong fit
  technically; the onboarding contract and KYB paperwork would consume
  most of our 60-hour budget before we wrote any code. Reconsider in
  FY27 if transaction volume justifies a platform negotiation.
- **Roll our own on top of an acquirer API (e.g. Worldpay direct)** —
  rejected on PCI scope. Storing a pan, even briefly, moves us from
  SAQ-A to SAQ-D. Not worth it for the fee saving at this volume.
- **Defer card payments; ship bank-transfer-only first** — rejected by
  product because conversion on bank transfer is ~40% of card in our
  segment.
