<?php
declare(strict_types=1);

require_once __DIR__ . '/../../_support/assert.php';

final class EnemyStats
{
    public function __construct(
        public int $health,
        public int $damage,
        public float $speed,
    ) {}
}

final class LevelConfig
{
    public function __construct(
        public string $name,
        public string $tileset,
        public string $musicTrack,
        public EnemyStats $enemyStats,
        /** @var list<string> */
        public array $hazards,
    ) {}

    public function __clone()
    {
        // deep-clone the parts that are mutable / nested
        $this->enemyStats = clone $this->enemyStats;
        // arrays of scalars are copied by value, so $this->hazards is fine
    }
}

final class LevelConfigRegistry
{
    /** @var array<string, LevelConfig> */
    private array $prototypes = [];

    public function register(string $key, LevelConfig $config): void { $this->prototypes[$key] = $config; }

    public function spawn(string $key): LevelConfig
    {
        $proto = $this->prototypes[$key] ?? throw new \RuntimeException("unknown level {$key}");
        return clone $proto;
    }
}

// ---- assertions -------------------------------------------------------------

$registry = new LevelConfigRegistry();
$registry->register('forest', new LevelConfig(
    name: 'Forest',
    tileset: 'forest_tiles.png',
    musicTrack: 'birds.ogg',
    enemyStats: new EnemyStats(health: 50, damage: 5, speed: 1.0),
    hazards: ['poison-vine'],
));

// designer wants two forest variants: one harder, one with extra hazards
$harder = $registry->spawn('forest');
$harder->name = 'Forest (hard)';
$harder->enemyStats->health = 75;

$dense = $registry->spawn('forest');
$dense->name = 'Forest (dense)';
$dense->hazards[] = 'pitfall';

// originals are untouched (deep clone working)
$original = $registry->spawn('forest');
pdp_assert_eq('Forest',           $original->name,                'original name unchanged');
pdp_assert_eq(50,                  $original->enemyStats->health,  'original enemy stats unchanged');
pdp_assert_eq(['poison-vine'],     $original->hazards,             'original hazards unchanged');

// variants distinct
pdp_assert_eq(75, $harder->enemyStats->health, 'hard variant health');
pdp_assert_eq(['poison-vine', 'pitfall'], $dense->hazards, 'dense variant hazards');
pdp_assert_true($harder->enemyStats !== $original->enemyStats, 'enemy stats deep-cloned');

pdp_done();
