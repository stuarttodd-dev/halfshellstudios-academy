# Chapter 4 — Exercise: validate a complex checkout form

**Course page:** [Build a robust validation boundary for a complex checkout form](http://127.0.0.1:38080/learn/sections/chapter-4-validation-form-requests/exercise-validate-complex-form)

## Dependencies

The `exists:products,id` rule needs a `products` table (see chapter 2 solution). Seed at least one product id for manual tests.

## Files

- `files/app/Http/Requests/StoreCheckoutRequest.php` — full contract: `authorize`, `prepareForValidation`, `rules`.
- `files/app/Http/Controllers/CheckoutController.php` — uses `$request->validated()` only.
- `files/routes/checkout.php` — single `POST /checkout` behind `auth`.

Wire the route into `routes/web.php` and add a feature test per the lesson (guest 403, invalid business payload 422, etc.).
