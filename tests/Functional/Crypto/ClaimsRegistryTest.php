<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Crypto;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Mockery;
use Windy\Guardian\Crypto\ClaimsRegistry;
use Windy\Guardian\Exceptions\InvalidConfiguration;
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
     * @covers \Windy\Guardian\Exceptions\InvalidConfiguration::claims
     */
    public function testUnknown(): void
    {
        /** @var ClaimsRegistry $registry */
        $registry  = $this->app->make(ClaimsRegistry::class);
        $exception = $registry->unknown('foo');

        $this->assertInstanceOf(InvalidConfiguration::class, $exception);
        $this->assertMatchesRegularExpression('/claims.*foo/s', $exception->getMessage());
    }
}
