<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Crypto;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Jose\Component\Signature\Algorithm\HS256;
use Mockery;
use Throwable;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\Claims;
use Windy\Guardian\Crypto\ClaimsRegistry;
use Windy\Guardian\Crypto\Key;
use Windy\Guardian\Crypto\KeysRegistry;
use Windy\Guardian\Exceptions\InvalidAuthorityConfigurationException;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Crypto\AuthoritiesRegistry
 */
class AuthoritiesRegistryTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->assertNotNull($this->app->make(AuthoritiesRegistry::class));
    }

    /**
     * @covers ::create
     *
     * @throws BindingResolutionException
     */
    public function testCreate(): void
    {
        $key = Mockery::mock(Key::class);
        $key->expects('getAlgorithm')->andReturns(new HS256());

        $keysRegistry = Mockery::mock(KeysRegistry::class);
        $keysRegistry
            ->expects('get')
            ->withArgs(['foo'])
            ->andReturns($key);

        $claimsRegistry = Mockery::mock(ClaimsRegistry::class);
        $claimsRegistry
            ->expects('get')
            ->withArgs(['bar'])
            ->andReturns(Mockery::mock(Claims::class));

        $registry = new AuthoritiesRegistry(
            $this->app->make(Container::class),
            $keysRegistry,
            $claimsRegistry
        );
        $this->assertNotNull($registry->create(['key' => 'foo', 'claims' => 'bar']));
    }

    /**
     * @covers ::unknown
     *
     * @throws Throwable
     */
    public function testUnknown(): void
    {
        /** @var AuthoritiesRegistry $registry */
        $registry = $this->app->make(AuthoritiesRegistry::class);
        $this->expectException(InvalidAuthorityConfigurationException::class);
        throw $registry->unknown('foo');
    }
}
