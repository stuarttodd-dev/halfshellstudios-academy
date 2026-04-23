<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

/** Intrinsic — shared per type. */
final class EnemyType
{
    public function __construct(
        public readonly string $name,
        public readonly int $maxHp,
        public readonly int $attack,
        public readonly int $defence,
        public readonly string $sprite,
        public readonly string $sound,
    ) {}
}

final class EnemyTypeRegistry
{
    /** @var array<string, EnemyType> */
    private array $byName = [];

    public function register(EnemyType $type): void { $this->byName[$type->name] = $type; }
    public function get(string $name): EnemyType { return $this->byName[$name] ?? throw new \RuntimeException("unknown {$name}"); }
    public function size(): int { return count($this->byName); }
}

/** Extrinsic — what changes per spawned enemy. */
final class Enemy
{
    public int $hp;
    public function __construct(
        public readonly EnemyType $type,
        public int $x,
        public int $y,
    ) {
        $this->hp = $type->maxHp;
    }

    public function takeDamage(int $amount): void
    {
        $effective = max(0, $amount - $this->type->defence);
        $this->hp = max(0, $this->hp - $effective);
    }
}

// ---- assertions -------------------------------------------------------------

$registry = new EnemyTypeRegistry();
$registry->register(new EnemyType('goblin', maxHp: 20, attack: 5,  defence: 2, sprite: 'goblin.png', sound: 'g.wav'));
$registry->register(new EnemyType('orc',    maxHp: 50, attack: 10, defence: 5, sprite: 'orc.png',    sound: 'o.wav'));
$registry->register(new EnemyType('troll',  maxHp: 90, attack: 15, defence: 8, sprite: 'troll.png',  sound: 't.wav'));

// 1000 enemies, 3 distinct types
$enemies = [];
for ($i = 0; $i < 1000; $i++) {
    $type = ['goblin', 'orc', 'troll'][$i % 3];
    $enemies[] = new Enemy($registry->get($type), x: $i, y: $i * 2);
}

pdp_assert_eq(3, $registry->size(), 'three shared EnemyType objects for 1000 enemies');

pdp_assert_true($enemies[0]->type === $enemies[3]->type, 'two goblins share the SAME EnemyType');
pdp_assert_true($enemies[0]->type !== $enemies[1]->type, 'goblin and orc have different EnemyType');

$enemies[0]->takeDamage(10);
pdp_assert_eq(12, $enemies[0]->hp, '20 - max(0, 10 - 2) = 12');
pdp_assert_eq(20, $enemies[3]->hp, 'sibling goblin unaffected — extrinsic state is per-instance');

$enemies[3]->x = 999;
pdp_assert_eq(0, $enemies[0]->x, 'positions are independent');

// measurement: enemies are tiny because heavy bits live in 3 shared types
$beforeBytes = strlen(serialize($enemies));
$inlined = static fn (Enemy $e) => (object) [
    'name' => $e->type->name, 'maxHp' => $e->type->maxHp, 'attack' => $e->type->attack,
    'defence' => $e->type->defence, 'sprite' => $e->type->sprite, 'sound' => $e->type->sound,
    'x' => $e->x, 'y' => $e->y, 'hp' => $e->hp,
];
$afterBytes = strlen(serialize(array_map($inlined, $enemies)));
pdp_assert_true($beforeBytes < $afterBytes, sprintf('shared types save bytes (shared=%d, inlined=%d)', $beforeBytes, $afterBytes));

pdp_done();
