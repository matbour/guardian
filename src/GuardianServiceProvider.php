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
use Windy\Guardian\Exceptions\InvalidGuardConfigurationException;
use Windy\Guardian\Utils\IO;

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
        $this->mergeConfigFrom(__DIR__ . '/../config/guardian.php', 'guardian');

        /** @var AuthManager $auth */
        $auth = $this->app->make('auth');

        $auth->extend('guardian', static function (Container $app, string $guard, array $config) {
            $provider = $app->make('auth')->createUserProvider($config['provider'] ?? null);

            if ($provider === null) {
                throw new InvalidGuardConfigurationException($guard);
            }

            $guardian = new GuardianRequestGuard(
                $app->make(AuthoritiesRegistry::class),
                $config,
                $app->make('request'),
                $provider
            );

            $app->refresh('request', $guardian, 'setRequest');

            return $guardian;
        });
    }
}
