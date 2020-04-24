<?php
/** @noinspection ClassConstantCanBeUsedInspection */

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseMigrations as LaravelDatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Laravel\Lumen\Testing\DatabaseMigrations as LumenDatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as LumenTestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (env('TEST_FRAMEWORK') === 'lumen') {
    class_alias(LumenTestCase::class, 'Hydra\\Testing\\TestCase');
    class_alias(LumenDatabaseMigrations::class, 'Hydra\\Testing\\DatabaseMigrations');
} else {
    class_alias(LaravelTestCase::class, 'Hydra\\Testing\\TestCase');
    class_alias(LaravelDatabaseMigrations::class, 'Hydra\\Testing\\DatabaseMigrations');
}
