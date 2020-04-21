<?php
/** @noinspection ClassConstantCanBeUsedInspection */

declare(strict_types=1);

use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Laravel\Lumen\Testing\TestCase as LumenTestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (env('TEST_FRAMEWORK') === 'lumen') {
    class_alias(LumenTestCase::class, 'Sandbox\\TestCase');
} else {
    class_alias(LaravelTestCase::class, 'Sandbox\\TestCase');
}
