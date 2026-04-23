<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: an Adapter is the WRONG answer here.
 *
 * The foreign API's interface (`geocode($line)->isValid()`) already
 * matches our domain need (`isValid($line): bool`) — same vocabulary,
 * same shape. There is no translation work to do.
 *
 * The actual smell in the starter is the `new ModernGeocoder(...)`
 * inside the use case: a hidden dependency wired to a global env var.
 * The fix is plain dependency injection through a thin domain
 * interface (`AddressGeocoder`). No translation, no adapter.
 *
 * If we DID need to adapt later (the SDK changes shape, we add a
 * second provider, the test suite needs an in-memory geocoder), the
 * domain interface is already there to receive the adapter. Today,
 * adding one would be a layer of code that translates A to A.
 */

/** Thin domain interface — same shape as the SDK, just our types. */
interface AddressGeocoder
{
    public function isValid(string $addressLine): bool;
}

/** Production wiring: the SDK already implements our shape; no adapter needed. */
final class ModernGeocoder implements AddressGeocoder
{
    public function __construct(public readonly string $apiKey) {}
    public function isValid(string $addressLine): bool
    {
        return $addressLine !== '' && !str_contains(strtolower($addressLine), 'narnia');
    }
}

final class AddressValidator
{
    public function __construct(private readonly AddressGeocoder $geocoder) {}
    public function isValid(string $line): bool { return $this->geocoder->isValid($line); }
}

// ---- assertions -------------------------------------------------------------

$validator = new AddressValidator(new ModernGeocoder('k'));

pdp_assert_true($validator->isValid('221B Baker Street, London'), 'real address validates');
pdp_assert_true(!$validator->isValid(''),                          'empty address invalidates');
pdp_assert_true(!$validator->isValid('Cair Paravel, Narnia'),      'Narnia invalidates (sanity check on the stub)');

// And to demonstrate the swap-without-adapter point: a tiny in-memory
// implementation slots straight in for tests, no adapter needed.
$mem = new class implements AddressGeocoder {
    public function isValid(string $line): bool { return $line === 'OK'; }
};
pdp_assert_eq(true,  (new AddressValidator($mem))->isValid('OK'),  'in-memory geocoder honours its rule');
pdp_assert_eq(false, (new AddressValidator($mem))->isValid('NOK'), 'in-memory geocoder honours its rule (negative)');

pdp_done('(Adapter was the wrong answer — see the comment block.)');
