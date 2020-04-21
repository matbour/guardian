<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\GenericUser;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Sandbox\User;
use Throwable;
use Windy\Guardian\Auth\GuardianRequestGuard;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\Authority;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Auth\GuardianRequestGuard
 */
class GuardianRequestGuardTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     * @covers ::user
     */
    public function testUser(): void
    {
        // Prepare the configuration
        $mock = Mockery::mock('overload:' . User::class);
        $mock->expects('getAuthIdentifierName');
        $mock->expects('newQuery->where->first')->andReturns(new GenericUser(['id' => 42]));

        /** @var Repository $config */
        $config = $this->app->make('config');
        $config->set('auth', [
            'defaults'  => [
                'guard' => 'guardian',
            ],
            'guards'    => [
                'guardian' => [
                    'driver'    => 'guardian',
                    'provider'  => 'users',
                    'authority' => 'default',
                ],
            ],
            'providers' => [
                'users' => [
                    'driver' => 'eloquent',
                    'model'  => $mock->mockery_getName(),
                ],
            ],
        ]);

        /** @var Authority $authority */
        $authority = $this->app->make(AuthoritiesRegistry::class)->default();
        $token     = $authority->sign(['sub' => 42]);

        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', "Bearer $token");

        $this->app->instance('request', $request);

        /** @var AuthManager $auth */
        $auth = $this->app->make('auth');

        /** @var RequestGuard $guard */
        $guard = $auth->guard();

        $this->assertEquals(42, $guard->id());
    }

    /**
     * @covers ::user
     *
     * @throws Throwable
     */
    public function testUserNoToken(): void
    {
        $request = Request::create('/', 'GET');

        /** @var GuardianRequestGuard|Mock $mock */
        $mock = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $mock->setRequest($request);
        $this->assertNull($mock->user());
    }

    /**
     * @covers ::user
     *
     * @throws Throwable
     */
    public function testUserNoSubClaim(): void
    {
        /** @var AuthoritiesRegistry $authorities */
        $authorities = $this->app->make(AuthoritiesRegistry::class);
        $token       = $authorities->sign([]);

        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', "Bearer $token");

        $instance = new GuardianRequestGuard(
            $authorities,
            [],
            $request,
            Mockery::mock(UserProvider::class)
        );
        $instance->setRequest($request);
        $this->assertNull($instance->user());
    }

    /**
     * @covers ::validate
     * @covers ::setRequest
     *
     * @throws Throwable
     */
    public function testValidate(): void
    {
        /** @var GuardianRequestGuard|Mock $mock */
        $mock = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $mock->expects('user')->andReturns(new GenericUser(['id' => 42]));
        $logged = $mock->validate(['request' => Request::create('/', 'GET')]);

        $this->assertTrue($logged);
    }
}
