<?php

declare(strict_types=1);

namespace Windy\Guardian;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\Serializer;
use Windy\Guardian\Auth\GuardianRequestGuard;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\ClaimsRegistry;
use Windy\Guardian\Crypto\KeysRegistry;
use Windy\Guardian\Utils\IO;
use function dirname;

/**
 * Service provider for the Guardian library.
 */
class GuardianServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Serializer::class, CompactSerializer::class);
        $this->app->singleton(KeysRegistry::class);
        $this->app->singleton(ClaimsRegistry::class);
        $this->app->singleton(AuthoritiesRegistry::class);
        $this->app->singleton(IO::class);
    }

    public function boot(): void
    {
        // Register Guardian configuration
        $guardianConfig = dirname(__DIR__) . '/config/guardian.php';

        $this->mergeConfigFrom($guardianConfig, 'guardian');
        $this->publishes([
            $guardianConfig => $this->app->basePath('config/guardian.php'),
        ]);

        // Register Guardian Request Guard
        /** @var AuthManager $auth */
        $auth = $this->app->make('auth');

        $auth->extend('guardian', static function (Container $app, string $guard, array $config) {
            return new GuardianRequestGuard($app, $guard, $config);
        });
    }
}
