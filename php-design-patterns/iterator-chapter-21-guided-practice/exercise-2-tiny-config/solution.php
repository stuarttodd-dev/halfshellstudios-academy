<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — three keys do not need an iteration abstraction.
 *
 * Iterator earns its keep when:
 *   - the storage is non-trivial (db cursor, paged API, stream)
 *   - lazy production matters (you might break early)
 *   - you want `foreach` to hide a cursor or generator
 *
 * A `['debug' => false, 'env' => 'prod', 'tz' => 'UTC']` is none of
 * those. PHP's `foreach` already iterates over the array. Wrapping it
 * in `IteratorAggregate` would be ceremony for ceremony's sake.
 */

final class Config
{
    /** @param array<string,mixed> $settings */
    public function __construct(public readonly array $settings = []) {}
}

$cfg = new Config(['debug' => false, 'env' => 'prod', 'tz' => 'UTC']);

$captured = [];
foreach ($cfg->settings as $k => $v) $captured[$k] = $v;

pdp_assert_eq(['debug' => false, 'env' => 'prod', 'tz' => 'UTC'], $captured, 'plain foreach over array works fine');

pdp_done('Iterator was the wrong answer here — see the comment block.');
