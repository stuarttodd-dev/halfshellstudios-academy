# Chapter 22 — Mediator (guided practice)

Mediator collapses N × N coupling between collaborating components
into N × 1: each component talks only to a hub, and the hub owns the
coordination logic. The trap is reaching for a hub when there are only
two collaborators — that's just direct collaboration.

| Exercise | Brief | Verdict |
| --- | --- | --- |
| 1 — Form coordinator | Fields cross-validate against each other | **Mediator fits** — `CheckoutFormMediator` knows the rules |
| 2 — Cart + totaliser | Two objects | **Trap.** Direct collaboration is fine |
| 3 — Multi-service workflow | Payment, email, analytics, audit, inventory chained | **Mediator fits** — `DefaultCheckoutMediator` orchestrates incl. rollback |

---

## Exercise 1 — Form coordinator

```php
$mediator = new CheckoutFormMediator();
$country  = new Field('country',  $mediator);
$postcode = new Field('postcode', $mediator);
$email    = new Field('email',    $mediator);
$mediator->register($country); $mediator->register($postcode); $mediator->register($email);

$country->set('UK');
$postcode->set('SW1A 1AA');  // re-validated by the mediator
$country->set('US');         // changing country re-validates postcode without it knowing
```

Fields hold *only* the mediator. The cross-field rule "UK postcode
when country is UK" lives in one place.

---

## Exercise 2 — Cart + totaliser (the trap)

### Verdict — Mediator is the wrong answer

Two objects = N − 1 = 1 collaborator each. Direct injection is the
right shape. A `CartMediator` would add an empty class without
removing any coupling.

---

## Exercise 3 — Multi-service workflow

```php
$mediator = new DefaultCheckoutMediator($email, $analytics, $audit, $inventory);
$mediator->setPayments(new PaymentService($mediator));

$mediator->checkout('o1', qty: 2, amountInPence: 5_000, paymentShouldSucceed: true);
// PaymentService -> mediator -> email + analytics + audit
$mediator->checkout('o2', qty: 1, amountInPence: 2_500, paymentShouldSucceed: false);
// PaymentService -> mediator -> inventory.release + failure-notice + analytics + audit
```

`PaymentService` only knows the mediator interface. The full flow
(including rollback on failure) is visible in one place — easy to
read, easy to change.

---

## Chapter rubric

For each non-trap exercise:

- a single mediator interface with one (or very few) coordination methods
- components holding only the mediator reference
- all coordination visible in one mediator class
- per-component tests with a fake mediator + integration tests of the workflow

For the trap: explain why two objects don't need a hub.

---

## How to run

```bash
cd php-design-patterns/mediator-chapter-22-guided-practice
php exercise-1-form-mediator/solution.php
php exercise-2-cart-totaliser/solution.php
php exercise-3-checkout-mediator/solution.php
```
