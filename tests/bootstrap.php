<?php
/** @noinspection ClassConstantCanBeUsedInspection */

declare(strict_types=1);

use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Laravel\Lumen\Testing\TestCase as LumenTestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php';

putenv('TEST_MODE=' . env('TEST_MODE', 'laravel'));

$mode = env('TEST_MODE', 'laravel');

if ($mode === 'lumen') {
    class_alias(LumenTestCase::class, 'Sandbox\\TestCase');
    require dirname(__DIR__) . '/sandbox/bootstrap/bootstrap.laravel.php';
} else {
    class_alias(LaravelTestCase::class, 'Sandbox\\TestCase');
    require dirname(__DIR__) . '/sandbox/bootstrap/bootstrap.lumen.php';
}
