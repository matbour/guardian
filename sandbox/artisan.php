#!/usr/bin/env php
<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

define('LARAVEL_START', microtime(true));

require dirname(__DIR__) . '/vendor/autoload.php';

if (env('TEST_FRAMEWORK') === 'lumen') {
    $app = require __DIR__ . '/bootstrap/bootstrap.lumen.php';
} else {
    $app = require __DIR__ . '/bootstrap/bootstrap.laravel.php';
}

$kernel = $app->make(Kernel::class);
$status = $kernel->handle($input = new ArgvInput(), new ConsoleOutput());

$kernel->terminate($input, $status);

exit($status);
