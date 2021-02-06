<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Crypto;

use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\HS256;
use Windy\Guardian\Crypto\Key;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Crypto\Key
 */
class KeyTest extends GuardianTestCase
{
    /** @var Key $instance The Key instance. */
    private $instance;

    public function setUp(): void
    {
        parent::setUp();

        $this->instance = new Key(JWKFactory::createOctKey(256), ['algorithm' => HS256::class]);
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->assertNotNull(
            new Key(JWKFactory::createOctKey(256), ['algorithm' => HS256::class])
        );
    }

    /**
     * @covers ::getAlgorithm
     */
    public function testAlgorithm(): void
    {
        $this->assertInstanceOf(HS256::class, $this->instance->getAlgorithm());
    }

    /**
     * @covers ::getThumbprint
     */
    public function testGetThumbprint(): void
    {
        $this->assertNotNull($this->instance->getThumbprint());
    }

    /**
     * @covers ::getJWK
     */
    public function testGetJWK(): void
    {
        $this->assertNotNull($this->instance->getJWK());
    }

    /**
     * @covers ::getPublicJWK
     */
    public function testGetPublicJWK(): void
    {
        $this->assertNotNull($this->instance->getPublicJWK());
    }
}
