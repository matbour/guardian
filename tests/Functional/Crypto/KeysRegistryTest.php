<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Crypto;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;
use Mockery;
use Windy\Guardian\Crypto\Key;
use Windy\Guardian\Crypto\KeyFactory;
use Windy\Guardian\Crypto\KeysRegistry;
use Windy\Guardian\Exceptions\InvalidConfiguration;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Crypto\KeysRegistry
 */
class KeysRegistryTest extends GuardianTestCase
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
        $container->expects('make')
            ->withArgs([KeyFactory::class])
            ->andReturns(Mockery::mock(KeyFactory::class));

        new KeysRegistry($container);
    }

    /**
     * @covers ::create
     *
     * @throws ValidationException
     */
    public function testCreate(): void
    {
        $config     = ['foo' => 'bar'];
        $keyFactory = Mockery::mock(KeyFactory::class);
        $keyFactory->expects('createFromConfig')->withArgs([$config])->andReturns(Mockery::mock(Key::class));
        $this->app->instance(KeyFactory::class, $keyFactory);

        /** @var KeysRegistry $registry */
        $registry = $this->app->make(KeysRegistry::class);
        $registry->create($config);
    }

    /**
     * @covers ::unknown
     * @covers \Windy\Guardian\Exceptions\InvalidConfiguration::key
     */
    public function testUnknown(): void
    {
        /** @var KeysRegistry $registry */
        $registry  = $this->app->make(KeysRegistry::class);
        $exception = $registry->unknown('foo');

        $this->assertInstanceOf(InvalidConfiguration::class, $exception);
        $this->assertMatchesRegularExpression('/keys.*foo/s', $exception->getMessage());
    }
}
