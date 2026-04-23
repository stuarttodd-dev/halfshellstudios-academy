<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

interface TileVisitor
{
    public function visitGrass(Grass $t): mixed;
    public function visitWater(Water $t): mixed;
    public function visitMountain(Mountain $t): mixed;
}

interface Tile { public function accept(TileVisitor $v): mixed; }

final class Grass    implements Tile { public function accept(TileVisitor $v): mixed { return $v->visitGrass($this); } }
final class Water    implements Tile { public function accept(TileVisitor $v): mixed { return $v->visitWater($this); } }
final class Mountain implements Tile { public function accept(TileVisitor $v): mixed { return $v->visitMountain($this); } }

final class WalkableCostVisitor implements TileVisitor
{
    public function visitGrass(Grass $t): int    { return 1; }
    public function visitWater(Water $t): int    { return PHP_INT_MAX; }
    public function visitMountain(Mountain $t): int { return 5; }
}

final class RenderColourVisitor implements TileVisitor
{
    public function visitGrass(Grass $t): string    { return '#3a7'; }
    public function visitWater(Water $t): string    { return '#39c'; }
    public function visitMountain(Mountain $t): string { return '#777'; }
}

final class FootstepSoundVisitor implements TileVisitor
{
    public function visitGrass(Grass $t): string    { return 'soft.wav'; }
    public function visitWater(Water $t): string    { return 'splash.wav'; }
    public function visitMountain(Mountain $t): string { return 'crunch.wav'; }
}

final class AiPathingWeightVisitor implements TileVisitor
{
    public function visitGrass(Grass $t): float    { return 1.0; }
    public function visitWater(Water $t): float    { return 100.0; }
    public function visitMountain(Mountain $t): float { return 5.0; }
}

/** Fifth operation added with zero edits to Grass / Water / Mountain. */
final class LightingAbsorptionVisitor implements TileVisitor
{
    public function visitGrass(Grass $t): float    { return 0.4; }
    public function visitWater(Water $t): float    { return 0.1; }
    public function visitMountain(Mountain $t): float { return 0.7; }
}

// ---- assertions -------------------------------------------------------------

$tiles = [new Grass(), new Water(), new Mountain()];

pdp_assert_eq([1, PHP_INT_MAX, 5], array_map(static fn (Tile $t) => $t->accept(new WalkableCostVisitor()), $tiles), 'walkable cost');
pdp_assert_eq(['#3a7', '#39c', '#777'], array_map(static fn (Tile $t) => $t->accept(new RenderColourVisitor()), $tiles), 'render colour');
pdp_assert_eq(['soft.wav', 'splash.wav', 'crunch.wav'], array_map(static fn (Tile $t) => $t->accept(new FootstepSoundVisitor()), $tiles), 'footstep sound');
pdp_assert_eq([1.0, 100.0, 5.0], array_map(static fn (Tile $t) => $t->accept(new AiPathingWeightVisitor()), $tiles), 'ai pathing weight');
pdp_assert_eq([0.4, 0.1, 0.7], array_map(static fn (Tile $t) => $t->accept(new LightingAbsorptionVisitor()), $tiles), 'new operation, zero edits to tiles');

pdp_done();
