<?php

declare(strict_types=1);

/**
 * Copies _laravel-skeleton into each chNN-exercise-... directory's laravel/ folder, merges files/ on top,
 * and wires routes/web.php to require routes/solution.php.
 *
 * Run from laravel-best-practices/: php scripts/materialize_laravel_apps.php
 */

$base = dirname(__DIR__);
$skeleton = $base . '/_laravel-skeleton';

if (! is_dir($skeleton)) {
    fwrite(STDERR, "Missing _laravel-skeleton. See README.\n");
    exit(1);
}

$chapters = glob($base . '/ch*-exercise-*', GLOB_ONLYDIR) ?: [];
sort($chapters);

$webTemplate = <<<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response(
        'Laravel exercise app — use the routes for this chapter (see ../README). ' .
        'GET /exercise for a quick health check.'
    );
});

Route::get('/exercise', fn () => 'ok');

if (file_exists(__DIR__.'/solution.php')) {
    require __DIR__.'/solution.php';
}

PHP;

foreach ($chapters as $dir) {
    if (! is_dir($dir . '/files')) {
        continue;
    }
    $name = basename($dir);
    $dest = $dir . '/laravel';
    if (is_dir($dest)) {
        passthru('rm -rf ' . escapeshellarg($dest) . ' 2>/dev/null', $code);
    }
    echo "Copying skeleton → {$name}/laravel\n";
    passthru('cp -R ' . escapeshellarg($skeleton) . ' ' . escapeshellarg($dest), $code);
    if ($code !== 0) {
        fwrite(STDERR, "cp failed for {$name}\n");
        exit(1);
    }
    if (file_exists($dest . '/.env')) {
        @unlink($dest . '/.env');
    }
    // Merge files/ (contents) over laravel
    $files = $dir . '/files';
    passthru('cp -R ' . escapeshellarg($files) . '/. ' . escapeshellarg($dest) . '/', $code);
    if ($code !== 0) {
        fwrite(STDERR, "cp files failed for {$name}\n");
        exit(1);
    }
    $solution = $dir . '/files/routes/solution.php';
    if (! is_file($solution)) {
        $stub = $dest . '/routes/solution.php';
        if (! is_file($stub)) {
            file_put_contents($stub, "<?php\ndeclare(strict_types=1);\n// No solution routes. Add files/routes/solution.php in the chapter or see SOLUTION.md.\n");
        }
    }
    file_put_contents($dest . '/routes/web.php', $webTemplate);
    // SQLite file for one-command setup
    $dbfile = $dest . '/database/database.sqlite';
    if (! is_file($dbfile)) {
        touch($dbfile);
    }
    echo "  done {$name}\n";
}

echo "All chapter laravel/ folders materialised. Next: run composer install in each app.\n";
