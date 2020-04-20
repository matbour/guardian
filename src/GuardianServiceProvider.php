<?php

declare(strict_types=1);

namespace Windy\Guardian;

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Support\ServiceProvider;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\Serializer;
use Windy\Guardian\Auth\GuardianUserResolver;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\ClaimsRegistry;
use Windy\Guardian\Crypto\KeyRegistry;

class GuardianServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Serializer::class, CompactSerializer::class);
        $this->app->singleton(KeyRegistry::class);
        $this->app->singleton(ClaimsRegistry::class);
        $this->app->singleton(AuthoritiesRegistry::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/guardian.php', 'guardian');

        /** @var Factory $auth */
        $auth = $this->app->make('auth');

        $auth->viaRequest(
            $this->app['config']->get('guardian.auth.driver_name', 'jwt'),
            $this->app->make(GuardianUserResolver::class)
        );
    }
}
