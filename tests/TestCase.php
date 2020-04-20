<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Tests;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Mathrix\Lumen\JWT\Providers\JWTServiceProvider;
use function dirname;
use function env;

/**
 * @property LaravelApplication|LumenApplication $app
 */
class TestCase extends \Sandbox\TestCase
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

        $app->register(JWTServiceProvider::class);

        return $app;
    }
}
