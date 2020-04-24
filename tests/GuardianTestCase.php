<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests;

use Hydra\Testing\TestCase;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Windy\Guardian\GuardianServiceProvider;
use function dirname;
use function env;

/**
 * @property LaravelApplication|LumenApplication $app
 */
abstract class GuardianTestCase extends TestCase
{
    /**
     * @return LaravelApplication|LumenApplication The Laravel or Lumen application.
     */
    public function createApplication()
    {
        $mode = env('TEST_FRAMEWORK');

        if ($mode !== 'lumen') {
            $app = require dirname(__DIR__) . '/sandbox/bootstrap/bootstrap.laravel.php';
            $app->make(Kernel::class)->bootstrap();
        } else {
            $app = require dirname(__DIR__) . '/sandbox/bootstrap/bootstrap.lumen.php';
        }

        return $app;
    }

    /**
     * Setup the application.
     */
    public function setUpApplication(): void
    {
        $this->app->register(GuardianServiceProvider::class);
    }

    public function setUp(): void
    {
        parent::setUp();

        $config = clone $this->app->make('config');
        $this->setUpApplication();

        $this->beforeApplicationDestroyed(function () use ($config): void {
            $this->app->instance('config', $config);
        });
    }
}
