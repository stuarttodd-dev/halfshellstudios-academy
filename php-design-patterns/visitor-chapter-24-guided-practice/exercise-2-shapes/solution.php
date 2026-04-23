<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/*
 * TRAP — methods on the types beat the Visitor ceremony here.
 *
 * Visitor pays off when:
 *   - the set of operations grows over time,
 *   - the set of types is stable,
 *   - keeping operations off the types is the goal (e.g. compilers).
 *
 * Two shapes (`Circle`, `Rectangle`) and two stable operations (`area`,
 * `perimeter`) are the opposite case. Each method is one short formula.
 * A Visitor would mean an interface, two visitor classes, two `accept`
 * methods, and a dispatch — to compute `pi r^2`.
 *
 * Methods on `Shape` are clearer.
 */

interface Shape
{
    public function area(): float;
    public function perimeter(): float;
}

final class Circle implements Shape
{
    public function __construct(public readonly float $radius) {}
    public function area(): float { return M_PI * $this->radius ** 2; }
    public function perimeter(): float { return 2 * M_PI * $this->radius; }
}

final class Rectangle implements Shape
{
    public function __construct(public readonly float $width, public readonly float $height) {}
    public function area(): float { return $this->width * $this->height; }
    public function perimeter(): float { return 2 * ($this->width + $this->height); }
}

// ---- assertions -------------------------------------------------------------

$c = new Circle(2);
pdp_assert_true(abs($c->area()      - (M_PI * 4))   < 1e-9, 'circle area');
pdp_assert_true(abs($c->perimeter() - (2 * M_PI * 2)) < 1e-9, 'circle perimeter');

$r = new Rectangle(3, 4);
pdp_assert_eq(12.0, $r->area(), 'rectangle area');
pdp_assert_eq(14.0, $r->perimeter(), 'rectangle perimeter');

pdp_done('Visitor was the wrong answer here — see the comment block.');
