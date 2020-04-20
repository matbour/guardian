<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Providers;

use Illuminate\Auth\AuthManager;
use Illuminate\Support\ServiceProvider;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\Serializer;
use Mathrix\Lumen\JWT\Auth\JWTUserResolver;
use Mathrix\Lumen\JWT\Commands\JWTBenchmarkCommand;
use Mathrix\Lumen\JWT\Commands\JWTKeyCommand;
use Mathrix\Lumen\JWT\Crypto\AuthoritiesRegistry;
use Mathrix\Lumen\JWT\Crypto\ClaimsRegistry;
use Mathrix\Lumen\JWT\Crypto\KeyRegistry;

class JWTServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/jwt.php', 'jwt');

        $this->commands([JWTKeyCommand::class, JWTBenchmarkCommand::class]);

        $this->app->singleton(Serializer::class, CompactSerializer::class);
        $this->app->singleton(KeyRegistry::class);
        $this->app->singleton(ClaimsRegistry::class);
        $this->app->singleton(AuthoritiesRegistry::class);

        /** @var AuthManager $auth */
        $auth = $this->app->make('auth');
        $auth->viaRequest($this->app['config']->get('jwt.auth.driver_name', 'jwt'), new JWTUserResolver());
    }
}
