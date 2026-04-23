<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface Expression
{
    /** @param array<string, bool> $ctx */
    public function evaluate(array $ctx): bool;
}

final class VarExpr implements Expression
{
    public function __construct(public readonly string $name) {}
    public function evaluate(array $ctx): bool { return $ctx[$this->name] ?? false; }
}

final class AndExpr implements Expression
{
    public function __construct(public readonly Expression $left, public readonly Expression $right) {}
    public function evaluate(array $ctx): bool { return $this->left->evaluate($ctx) && $this->right->evaluate($ctx); }
}

final class OrExpr implements Expression
{
    public function __construct(public readonly Expression $left, public readonly Expression $right) {}
    public function evaluate(array $ctx): bool { return $this->left->evaluate($ctx) || $this->right->evaluate($ctx); }
}

final class NotExpr implements Expression
{
    public function __construct(public readonly Expression $inner) {}
    public function evaluate(array $ctx): bool { return !$this->inner->evaluate($ctx); }
}

// ---- assertions -------------------------------------------------------------

$expr = new AndExpr(new VarExpr('isAdmin'), new OrExpr(new VarExpr('isOwner'), new VarExpr('isEditor')));

pdp_assert_eq(false, $expr->evaluate(['isAdmin' => false, 'isOwner' => true,  'isEditor' => false]), 'admin missing');
pdp_assert_eq(false, $expr->evaluate(['isAdmin' => true,  'isOwner' => false, 'isEditor' => false]), 'admin but not owner/editor');
pdp_assert_eq(true,  $expr->evaluate(['isAdmin' => true,  'isOwner' => true,  'isEditor' => false]), 'admin + owner');
pdp_assert_eq(true,  $expr->evaluate(['isAdmin' => true,  'isOwner' => false, 'isEditor' => true]),  'admin + editor');

// per-leaf tests
pdp_assert_eq(true,  (new VarExpr('x'))->evaluate(['x' => true]),  'leaf true');
pdp_assert_eq(false, (new VarExpr('x'))->evaluate(['x' => false]), 'leaf false');
pdp_assert_eq(false, (new VarExpr('missing'))->evaluate([]), 'missing key defaults to false');

// not + nesting
$nested = new NotExpr(new OrExpr(new VarExpr('a'), new VarExpr('b')));
pdp_assert_eq(true,  $nested->evaluate(['a' => false, 'b' => false]), 'NOT(a OR b) when both false');
pdp_assert_eq(false, $nested->evaluate(['a' => true,  'b' => false]), 'NOT(a OR b) when a');

pdp_done();
