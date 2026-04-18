<?php
declare(strict_types=1);

$stub = <<<'PHP'
#!/usr/bin/env php
<?php
Phar::mapPhar();
__HALT_COMPILER();
PHP;

echo str_contains($stub, 'mapPhar') && str_contains($stub, '__HALT_COMPILER') ? 'phar' : 'no';
