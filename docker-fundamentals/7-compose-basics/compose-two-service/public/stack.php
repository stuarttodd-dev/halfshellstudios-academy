<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

echo "compose stack (nginx + php + db + redis)\n";
echo 'DB_HOST=' . (getenv('DB_HOST') ?: '(unset)') . "\n";
echo 'REDIS_HOST=' . (getenv('REDIS_HOST') ?: '(unset)') . "\n";
echo 'MAIL_HOST=' . (getenv('MAIL_HOST') ?: '(unset)') . "\n";
