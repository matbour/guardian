<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional;

use Illuminate\Contracts\Config\Repository;
use Jose\Component\Signature\Serializer\Serializer;
use Windy\Guardian\Auth\GuardianRequestGuard;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\ClaimsRegistry;
use Windy\Guardian\Crypto\KeysRegistry;
use Windy\Guardian\Exceptions\InvalidGuardConfigurationException;
use Windy\Guardian\GuardianServiceProvider;
use Windy\Guardian\Tests\GuardianTestCase;
use Windy\Guardian\Utils\IO;

/**
 * @coversDefaultClass \Windy\Guardian\GuardianServiceProvider
 */
class GuardianServiceProviderTest extends GuardianTestCase
{
    /** @var GuardianServiceProvider $instance The service provider instance under test. */
    private $instance;

    public function setUpApplication(): void
    {
        // Overridden since we want to test the GuardianServiceProvider.
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->instance = new GuardianServiceProvider($this->app);
    }

    /**
     * @covers ::register
     */
    public function testRegister(): void
    {
        $this->instance->register();

        $this->assertTrue($this->app->bound(Serializer::class));
        $this->assertTrue($this->app->bound(KeysRegistry::class));
        $this->assertTrue($this->app->bound(ClaimsRegistry::class));
        $this->assertTrue($this->app->bound(AuthoritiesRegistry::class));
        $this->assertTrue($this->app->bound(IO::class));
    }

    /**
     * @covers ::boot
     */
    public function testBoot(): void
    {
        $this->instance->register(); // required load all dependencies

        /** @var Repository $config */
        $config = $this->app->make('config');

        $this->assertNull($config->get('guardian'));
        $this->instance->boot();
        $this->assertNotNull($config->get('guardian'));
    }

    /**
     * @covers ::boot
     */
    public function testBootGetGuard(): void
    {
        $this->app->register(GuardianServiceProvider::class);

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
                    'model'  => 'Sandbox\\User',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            GuardianRequestGuard::class,
            $this->app->make('auth')->guard('guardian')
        );
    }

    /**
     * @covers ::boot
     */
    public function testBootNoGuardProvider(): void
    {
        $this->app->register(GuardianServiceProvider::class);

        /** @var Repository $config */
        $config = $this->app->make('config');
        $config->set('auth', [
            'guards' => [
                'guardian' => [
                    'driver'    => 'guardian',
                    //'provider'  => 'users', // no provider
                    'authority' => 'default',
                ],
            ],
        ]);

        $this->expectException(InvalidGuardConfigurationException::class);
        $this->app->make('auth')->guard('guardian');
    }
}
