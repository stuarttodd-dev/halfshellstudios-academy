#!/usr/bin/env php
<?php

// Illustrates a Laravel-shaped one-off: same bind mount, different -w path.
if (in_array('--version', $argv ?? [], true)) {
    echo "Laravel Framework demo-stub (bind-mounted project)\n";
    exit(0);
}

fwrite(STDERR, "Usage: php bin/artisan-stub.php --version\n");
exit(1);
