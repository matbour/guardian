<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Auth;

use App\User;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;
use ReflectionClass;
use ReflectionException;
use Throwable;
use Windy\Guardian\Auth\GuardianRequestGuard;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\Authority;
use Windy\Guardian\Exceptions\InvalidGuardConfigurationException;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Auth\GuardianRequestGuard
 */
class GuardianRequestGuardTest extends GuardianTestCase
{
    private const CREDENTIALS = ['foo' => 'bar'];
    private const CONFIG      = [
        'defaults'  => ['guard' => 'guardian'],
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
                'model'  => User::class,
            ],
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->app->make('config')->set('auth', self::CONFIG);
    }

    /**
     * Set a property of the guard.
     *
     * @param MockInterface $guard    The guard.
     * @param string        $property The property name.
     * @param mixed         $value    The property value.
     *
     * @throws ReflectionException
     */
    private function set(MockInterface $guard, string $property, $value): void
    {
        // Bypass the protected visibility
        $class         = new ReflectionClass($guard->mockery_getName());
        $lastAttempted = $class->getProperty($property);
        $lastAttempted->setAccessible(true);
        $lastAttempted->setValue($guard, $value);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $container = Mockery::mock(Container::class);

        $guard = new GuardianRequestGuard(
            $container,
            'guard',
            self::CONFIG['guards']['guardian']
        );

        $this->assertNotNull($guard);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructInvalidConfig(): void
    {
        $container = Mockery::mock(Container::class);
        $this->expectException(InvalidGuardConfigurationException::class);

        new GuardianRequestGuard($container, 'guard', ['driver' => 'guardian']);
    }

    /**
     * @covers ::getProvider
     *
     * @throws BindingResolutionException
     */
    public function testGetProvider(): void
    {
        $provider = Mockery::mock(UserProvider::class);
        $auth     = Mockery::mock(Auth::class);
        $auth->expects('createUserProvider')
            ->withArgs([self::CONFIG['guards']['guardian']['provider']])
            ->andReturns($provider);
        $container = Mockery::mock(Container::class);
        $container->expects('make')
            ->withArgs(['auth'])
            ->andReturns($auth);

        $guard = new GuardianRequestGuard($container, 'guard', self::CONFIG['guards']['guardian']);

        $this->assertEquals($provider, $guard->getProvider());
        $this->assertEquals($provider, $guard->getProvider());
    }

    /**
     * @covers ::getAuthority
     *
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function testGetAuthority(): void
    {
        $authority = Mockery::mock(Authority::class);
        $registry  = Mockery::mock(AuthoritiesRegistry::class);
        $registry->expects('get')
            ->withArgs([self::CONFIG['guards']['guardian']['authority']])
            ->andReturns($authority);
        $container = Mockery::mock(Container::class);
        $container->expects('make')
            ->withArgs([AuthoritiesRegistry::class])
            ->andReturns($registry);

        $guard = new GuardianRequestGuard($container, 'guard', self::CONFIG['guards']['guardian']);

        $this->assertEquals($authority, $guard->getAuthority());
        $this->assertEquals($authority, $guard->getAuthority());
    }

    /**
     * @covers ::getRequest
     *
     * @throws BindingResolutionException
     */
    public function testGetRequest(): void
    {
        $container = Mockery::mock(Container::class);
        $request   = Request::createFromGlobals();
        $container->expects('make')->withArgs(['request'])->andReturns($request);

        $guard = new GuardianRequestGuard($container, 'guard', self::CONFIG['guards']['guardian']);

        $this->assertEquals($request, $guard->getRequest());
    }

    /**
     * @covers ::user
     *
     * @throws Throwable
     */
    public function testUserAttemptTrue(): void
    {
        /** @var GuardianRequestGuard|MockInterface $mock */
        $mock = Mockery::mock(GuardianRequestGuard::class)->makePartial();

        $mock->expects('attempt')->andReturnTrue();
        $this->assertNull($mock->user());
    }

    /**
     * @covers ::user
     *
     * @throws Throwable
     */
    public function testUserAttemptFalse(): void
    {
        /** @var GuardianRequestGuard|MockInterface $mock */
        $mock = Mockery::mock(GuardianRequestGuard::class)->makePartial();

        $mock->expects('attempt')->andReturnFalse();
        $this->assertNull($mock->user());
    }

    /**
     * @covers ::user
     *
     * @throws Throwable
     */
    public function testUserAlreadySet(): void
    {
        /** @var GuardianRequestGuard|MockInterface $mock */
        $mock = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $user = new GenericUser(['id' => 42]);
        $mock->login($user);

        $this->assertEquals($user, $mock->user());
    }

    /**
     * @covers ::validate
     * @covers ::validateRequest
     *
     * @throws Throwable
     */
    public function testValidateRequestNoCredentials(): void
    {
        $token = 'Bearer 123456';
        $id    = 42;

        $request = Mockery::mock(Request::class);
        $request->expects('bearerToken')->withNoArgs()->andReturns($token);

        $authority = Mockery::mock(Authority::class);
        $authority->expects('verify')->withArgs([$token, true]);
        $authority->expects('check')->withArgs([$token, true]);
        $authority->expects('unserialize->getPayload')->withNoArgs()->andReturns("{\"sub\":\"$id\"}");

        $provider = Mockery::mock(UserProvider::class);
        $provider->expects('retrieveById')->withArgs([(string)$id]);

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $guard->expects('getRequest')->withNoArgs()->andReturns($request);
        $guard->expects('getAuthority')->withNoArgs()->times(3)->andReturns($authority);
        $guard->expects('getProvider')->withNoArgs()->andReturns($provider);

        $this->assertTrue($guard->validate());
    }

    /**
     * @covers ::validate
     * @covers ::validateRequest
     *
     * @throws Throwable
     */
    public function testValidateRequestNoSubClaim(): void
    {
        $token = 'Bearer 123456';

        $request = Mockery::mock(Request::class);
        $request->expects('bearerToken')->withNoArgs()->andReturns($token);

        $authority = Mockery::mock(Authority::class);
        $authority->expects('verify')->withArgs([$token, true]);
        $authority->expects('check')->withArgs([$token, true]);
        $authority->expects('unserialize->getPayload')->withNoArgs()->andReturns('{}');

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $guard->expects('getRequest')->withNoArgs()->andReturns($request);
        $guard->expects('getAuthority')->withNoArgs()->times(3)->andReturns($authority);

        $this->assertFalse($guard->validate());
    }

    /**
     * @covers ::validate
     * @covers ::validateRequest
     *
     * @throws Throwable
     */
    public function testValidateRequestNoToken(): void
    {
        $request = Mockery::mock(Request::class);
        $request->expects('bearerToken')->withNoArgs()->andReturnNull();

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->assertFalse($guard->validate(['request' => $request]));
    }

    /**
     * @covers ::validate
     *
     * @throws Throwable
     */
    public function testValidateRequestFromCredentials(): void
    {
        $request = Request::createFromGlobals();

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $guard->expects('validateRequest')->withArgs([$request])->andReturnTrue();

        $this->assertTrue($guard->validate(['request' => $request]));
    }

    /**
     * @covers ::validate
     * @covers ::validateCredentials
     *
     * @throws Throwable
     */
    public function testValidateUserCredentials(): void
    {
        $credentials = [
            'email'    => 'mathieu@mathrix.fr',
            'password' => '123456',
        ];
        $user        = new GenericUser(['id' => 42]);

        $provider = Mockery::mock(UserProvider::class);
        $provider->expects('retrieveByCredentials')->withArgs([$credentials])->andReturns($user);
        $provider->expects('validateCredentials')->withAnyArgs()->andReturnFalse();

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $guard->expects('getProvider')->withNoArgs()->andReturns($provider)->twice();

        $this->assertFalse($guard->validate($credentials));
    }

    /**
     * @covers ::login
     *
     * @throws Throwable
     */
    public function testLogin(): void
    {
        $user = new GenericUser(['id' => 42]);
        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $guard->expects('setUser')->withArgs([$user]);

        $guard->login($user);
    }

    /**
     * @covers ::loginUsingId
     *
     * @throws Throwable
     */
    public function testLoginUsingId(): void
    {
        $id   = 42;
        $user = new GenericUser(['id' => $id]);

        $provider = Mockery::mock(UserProvider::class);
        $provider->expects('retrieveById')->withArgs([$id])->andReturns($user);

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $guard->expects('getProvider')->withNoArgs()->andReturns($provider);
        $guard->expects('login')->withArgs([$user]);

        $guard->loginUsingId($id);
    }

    /**
     * @covers ::attempt
     *
     * @throws Throwable
     */
    public function testAttemptValidateTrue(): void
    {
        $user = new GenericUser([]);

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $guard->expects('validate')
            ->withArgs([self::CREDENTIALS])
            ->andReturnTrue();
        $guard->expects('login')->withArgs([$user]);
        $this->set($guard, 'lastAttempted', $user);

        $this->assertTrue($guard->attempt(self::CREDENTIALS));
    }

    /**
     * @covers ::attempt
     *
     * @throws Throwable
     */
    public function testAttemptValidateFalse(): void
    {
        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $guard->expects('validate')
            ->withArgs([self::CREDENTIALS])
            ->andReturnFalse();

        $this->assertFalse($guard->attempt(self::CREDENTIALS));
    }

    /**
     * @covers ::once
     *
     * @throws Throwable
     */
    public function testOnce(): void
    {
        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $guard->expects('attempt')->withArgs([self::CREDENTIALS]);

        $guard->once(self::CREDENTIALS);
    }

    /**
     * @covers ::onceUsingId
     *
     * @throws BindingResolutionException
     */
    public function testOnceUsingId(): void
    {
        $id   = 42;
        $user = new GenericUser([]);

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $guard->expects('loginUsingId')->withArgs([$id])->andReturns($user);
        $guard->setUser($user);

        $this->assertEquals($user, $guard->onceUsingId($id));
    }

    /**
     * @covers ::onceUsingId
     *
     * @throws BindingResolutionException
     * @throws ReflectionException
     */
    public function testOnceUsingIdNullUser(): void
    {
        $id = 42;

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $guard->expects('loginUsingId')->withArgs([$id])->andReturns(null);
        $this->set($guard, 'user', null);

        $this->assertFalse($guard->onceUsingId($id));
    }

    /**
     * @covers ::viaRemember
     */
    public function testViaRemember(): void
    {
        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();

        $this->assertFalse($guard->viaRemember());
    }

    /**
     * @covers ::logout
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testLogout(): void
    {
        $user = new GenericUser([]);

        /** @var GuardianRequestGuard|Mock $guard */
        $guard = Mockery::mock(GuardianRequestGuard::class)->makePartial();
        $guard->expects('attempt')->withNoArgs()->andReturnFalse();
        $this->set($guard, 'user', $user);
        $this->assertNotNull($guard->user());

        $guard->logout();

        $this->assertNull($guard->user());
    }
}
