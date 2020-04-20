<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Crypto;

use ArrayIterator;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Support\Arrayable;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\JWS;
use Mockery;
use Mockery\MockInterface;
use Windy\Guardian\Crypto\Authority;
use Windy\Guardian\Crypto\Claims;
use Windy\Guardian\Crypto\Key;
use Windy\Guardian\Tests\GuardianTestCase;
use function base64_decode;
use function base64_encode;
use function explode;
use function file_get_contents;
use function fopen;
use function json_decode;

/**
 * @coversDefaultClass \Windy\Guardian\Crypto\Authority
 */
class AuthorityTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        /** @var Key|MockInterface $key */
        $key = Mockery::mock(Key::class);
        $key->expects('getAlgorithm')->andReturns(new HS256());
        /** @var Claims|MockInterface $claims */
        $claims = Mockery::mock(Claims::class);

        new Authority($key, $claims);
    }

    /**
     * @return mixed[][] The create payload dataset.
     */
    public function createPayloadDataset(): array
    {
        return [
            [
                fopen(__FILE__, 'rb'),
                ['data' => file_get_contents(__FILE__)],
            ],
            [
                123,
                ['data' => '123'],
            ],
            [
                'foo',
                ['data' => 'foo'],
            ],
            [
                ['foo' => 'bar', 'bar' => 'baz'],
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
            [
                new GenericUser(['id' => 42]),
                ['sub' => 42],
            ],
            [
                new class implements Arrayable {
                    /**
                     * @return string[]
                     */
                    public function toArray(): array
                    {
                        return ['foo' => 'bar', 'bar' => 'baz'];
                    }
                },
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
            [
                new ArrayIterator(['foo' => 'bar', 'bar' => 'baz']),
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
        ];
    }

    /**
     * @dataProvider createPayloadDataset
     * @covers ::payload
     *
     * @param mixed   $payload         The payload to test.
     * @param mixed[] $expectedPayload The expected generated payload.
     */
    public function testPayload($payload, array $expectedPayload): void
    {
        $authority = new Authority(
            new Key(JWKFactory::createOctKey(256), ['algorithm' => HS256::class]),
            new Claims(['iss' => 'foo'])
        );

        $expectedPayload['iss'] = 'foo';

        $this->assertEquals($expectedPayload, $authority->payload($payload));
    }

    /**
     * @covers ::sign
     */
    public function testSign(): void
    {
        $authority = new Authority(
            new Key(JWKFactory::createOctKey(256), ['algorithm' => HS256::class]),
            new Claims([])
        );

        $user    = new GenericUser(['id' => 42]);
        $parts   = explode('.', $authority->sign($user, true));
        $headers = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);

        $this->assertArrayHasKey('alg', $headers);
        $this->assertArrayHasKey('sub', $payload);
        $this->assertEquals(42, $payload['sub']);
        $this->assertIsString($parts[2]);
        $this->assertInstanceOf(JWS::class, $authority->sign($user, false));
    }

    /**
     * @return mixed[][]
     */
    public function createUnserialize(): array
    {
        $payload = '{"sub":"42"}';

        return [
            [base64_encode('{"alg":"HS256"}') . '.' . base64_encode($payload) . '.'],
            [new JWS($payload)],
        ];
    }

    /**
     * @dataProvider createUnserialize
     * @covers ::unserialize
     *
     * @param JWS|string $jws The JWT to unserialize.
     */
    public function testUnserialize($jws): void
    {
        $authority = new Authority(
            new Key(JWKFactory::createOctKey(256), ['algorithm' => HS256::class]),
            new Claims([])
        );

        $this->assertEquals('{"sub":"42"}', $authority->unserialize($jws)->getPayload());
    }
}
