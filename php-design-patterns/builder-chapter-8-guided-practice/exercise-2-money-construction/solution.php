<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/**
 * VERDICT: a Builder is the WRONG answer here.
 *
 * `new Money($amountInPence, $currency)` has TWO parameters with
 * obvious names and clear units. There is:
 *
 *   - no growing list of optional knobs,
 *   - no validation that requires multi-step assembly,
 *   - no domain ergonomics that "amount, currency" cannot express.
 *
 * Two-line construction is what NAMED ARGUMENTS are for, and a couple
 * of static factory methods carry domain meaning more clearly than a
 * builder ever could.
 *
 * Builder earns its place when:
 *   - the constructor has 5+ parameters and many are optional;
 *   - groups of parameters need to be set together (credentials, SSL);
 *   - the finished object benefits from late validation across fields.
 *
 * None of that applies to Money.
 */

final class Money
{
    public function __construct(
        public readonly int $amountInPence,
        public readonly string $currency,
    ) {
        if (strlen($currency) !== 3) throw new \InvalidArgumentException('currency must be ISO 4217 (3 chars)');
    }

    /** Static factory methods are MORE expressive than a builder for two parameters. */
    public static function gbp(int $pence): self { return new self($pence, 'GBP'); }
    public static function usd(int $cents): self { return new self($cents, 'USD'); }
    public static function eur(int $cents): self { return new self($cents, 'EUR'); }
}

// ---- assertions -------------------------------------------------------------

$fiver = Money::gbp(500);
pdp_assert_eq(500,   $fiver->amountInPence, 'gbp amount');
pdp_assert_eq('GBP', $fiver->currency,      'gbp currency');

// Named arguments cover the rare case where a static factory does not exist.
$rare = new Money(amountInPence: 12345, currency: 'JPY');
pdp_assert_eq(12345, $rare->amountInPence, 'named-args amount');
pdp_assert_eq('JPY', $rare->currency,      'named-args currency');

pdp_assert_throws(\InvalidArgumentException::class, fn () => new Money(100, 'POUND'), 'invalid currency rejected');

pdp_done('(Builder was the wrong answer — see the comment block.)');
