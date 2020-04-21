<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Crypto;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Throwable;
use Windy\Guardian\Crypto\Registry;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Crypto\Registry
 */
class RegistryTest extends GuardianTestCase
{
    /** @var Repository $config */
    private $config;
    /** @var Registry|MockObject */
    private $instance;

    public function setUp(): void
    {
        parent::setUp();

        $this->config   = $this->app->make('config');
        $this->instance = new class ($this->app, 'mock') extends Registry {
            /**
             * @param mixed[] $config The provided configuration.
             *
             * @return MockInterface The newly created MockInterface.
             */
            public function create(array $config): MockInterface
            {
                return Mockery::mock()->allows(['getConfig' => $config]);
            }

            public function unknown(string $name): Throwable
            {
                return new RuntimeException('Mock');
            }
        };

        $original = clone $this->config;
        $this->beforeApplicationDestroyed(function () use ($original): void {
            $this->app->instance('config', $original);
        });

        $this->config->set('guardian.mocks.foo', ['foo' => 'bar']);
        $this->config->set('guardian.defaults.mock', 'foo');
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $container = Mockery::mock(Container::class);
        $container->expects('make')->withArgs(['config']);
        Mockery::mock(Registry::class, [$container, 'mock']);
    }

    /**
     * @covers ::exists
     */
    public function testExists(): void
    {
        $this->assertTrue($this->instance->exists('foo'));
        $this->assertFalse($this->instance->exists('bar'));
    }

    /**
     * @covers ::get
     *
     * @throws Throwable
     */
    public function testGet(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->instance->get('foo')->getConfig());

        // Uses default and cache
        $this->assertEquals(['foo' => 'bar'], $this->instance->get()->getConfig());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Mock');
        $this->instance->get('bar');
    }

    /**
     * @covers ::default
     *
     * @throws Throwable
     */
    public function testDefault(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->instance->default()->getConfig());
    }

    /**
     * @covers ::__call
     */
    public function testCall(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->instance->getConfig());
    }
}
