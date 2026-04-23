<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — Flyweight only earns its keep at scale.
 *
 * Flyweight pays off when the *same intrinsic data* would otherwise be
 * duplicated thousands or millions of times. With ~50 settings in the
 * entire app, the saving is invisible: a `Setting` is small, and there
 * is no duplication problem to solve.
 *
 * Adding a registry/factory and an indirection per access would buy
 * the project nothing but a more complex API for a config dictionary.
 *
 * Save Flyweight for forum posts with shared author profiles, game
 * sprites, glyphs in a text editor — places with real cardinality.
 */

final class Setting
{
    public function __construct(
        public readonly string $key,
        public readonly string $value,
        public readonly string $section,
    ) {}
}

$settings = [
    new Setting('mail.driver', 'smtp', 'mail'),
    new Setting('mail.host',   'mx',   'mail'),
    new Setting('cache.ttl',   '60',   'cache'),
];

pdp_assert_eq('smtp', $settings[0]->value, 'plain object access works');
pdp_assert_eq(3, count($settings), 'no registry needed at this scale');

pdp_done('Flyweight was the wrong answer here — see the comment block.');
