<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Sandbox\TestCase;
use Windy\Guardian\GuardianServiceProvider;
use function dirname;
use function env;

/**
 * @property LaravelApplication|LumenApplication $app
 */
abstract class GuardianTestCase extends TestCase
{
    /**
     * @return mixed
     */
    public function createApplication()
    {
        $mode = env('TEST_MODE');

        if ($mode === 'lumen') {
            $app = require dirname(__DIR__) . '/sandbox/bootstrap/bootstrap.lumen.php';
        } else {
            $app = require dirname(__DIR__) . '/sandbox/bootstrap/bootstrap.laravel.php';
        }

        $app->register(GuardianServiceProvider::class);

        return $app;
    }
}
