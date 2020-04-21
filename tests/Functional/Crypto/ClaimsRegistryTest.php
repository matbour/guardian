<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Crypto;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Mockery;
use Throwable;
use Windy\Guardian\Crypto\ClaimsRegistry;
use Windy\Guardian\Exceptions\InvalidClaimsConfigurationException;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Crypto\ClaimsRegistry
 */
class ClaimsRegistryTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     *
     * @throws BindingResolutionException
     */
    public function testConstruct(): void
    {
        $container = Mockery::mock(Container::class);
        $container->expects('make')
            ->withArgs(['config'])
            ->andReturns(Mockery::mock(Repository::class));

        new ClaimsRegistry($container);
    }

    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        /** @var ClaimsRegistry $registry */
        $registry = $this->app->make(ClaimsRegistry::class);
        $this->assertNotNull($registry->create(['foo' => 'bar']));
    }

    /**
     * @covers ::unknown
     *
     * @throws Throwable
     */
    public function testUnknown(): void
    {
        /** @var ClaimsRegistry $registry */
        $registry = $this->app->make(ClaimsRegistry::class);
        $this->expectException(InvalidClaimsConfigurationException::class);
        throw $registry->unknown('foo');
    }
}
