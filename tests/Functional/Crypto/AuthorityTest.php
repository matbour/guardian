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
use Windy\Guardian\Exceptions\InvalidClaimException;
use Windy\Guardian\Exceptions\InvalidSignatureException;
use Windy\Guardian\Tests\GuardianTestCase;
use function base64_decode;
use function base64_encode;
use function explode;
use function file_get_contents;
use function fopen;
use function json_decode;
use function strrev;

/**
 * @coversDefaultClass \Windy\Guardian\Crypto\Authority
 */
class AuthorityTest extends GuardianTestCase
{
    private $authority;

    private function getAuthority(): Authority
    {
        if ($this->authority !== null) {
            return $this->authority;
        }

        return $this->authority = new Authority(
            new Key(JWKFactory::createOctKey(256), ['algorithm' => HS256::class]),
            new Claims(['iss' => 'foo'])
        );
    }

    private function getUser(): GenericUser
    {
        return new GenericUser(['id' => 42]);
    }

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
     * @return mixed[][] The dataset for {@see testPayload}.
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
        $authority = $this->getAuthority();

        $expectedPayload['iss'] = 'foo';

        $this->assertEquals($expectedPayload, $authority->payload($payload));
    }

    /**
     * @covers ::sign
     */
    public function testSignSerialize(): void
    {
        $parts   = explode('.', $this->getAuthority()->sign($this->getUser(), true));
        $headers = json_decode(base64_decode($parts[0], true), true);
        $payload = json_decode(base64_decode($parts[1], true), true);

        $this->assertArrayHasKey('alg', $headers);
        $this->assertArrayHasKey('sub', $payload);
        $this->assertEquals(42, $payload['sub']);
        $this->assertIsString($parts[2]);
    }

    /**
     * @covers ::sign
     */
    public function testSignJWS(): void
    {
        $this->assertInstanceOf(JWS::class, $this->getAuthority()->sign($this->getUser(), false));
    }

    /**
     * @return mixed[][]
     */
    public function createUnserializeDataset(): array
    {
        $payload = '{"iss":"foo","sub":"42"}';

        return [
            [base64_encode('{"alg":"HS256"}') . '.' . base64_encode($payload) . '.', $payload],
            [new JWS($payload), $payload],
        ];
    }

    /**
     * @dataProvider createUnserializeDataset
     * @covers ::unserialize
     *
     * @param JWS|string $jws             The JWT to unserialize.
     * @param string     $expectedPayload The expected JWT payload.
     */
    public function testUnserialize($jws, string $expectedPayload): void
    {
        $this->assertEquals(
            $expectedPayload,
            $this->getAuthority()->unserialize($jws)->getPayload()
        );
    }

    /**
     * @return mixed[][] The dataset for {@see testVerify}.
     */
    public function createVerifyDataset(): array
    {
        $authority = $this->getAuthority();
        $valid     = $authority->sign($this->getUser());
        $parts     = explode('.', $valid);
        $invalid   = "{$parts[0]}.{$parts[1]}." . strrev($parts[2]);

        return [
            [$authority, $valid, true],
            [$authority, $invalid, false],
            [$authority, $invalid, false, InvalidSignatureException::class],
        ];
    }

    /**
     * @dataProvider createVerifyDataset
     * @covers ::verify
     * @covers \Windy\Guardian\Exceptions\InvalidSignatureException
     *
     * @param Authority   $authority         The Authority to use.
     * @param string      $jws               The JWS to check.
     * @param bool        $expectValid       If the JWS is expected to be valid.
     * @param string|null $expectedException The expected exception, if any.
     */
    public function testVerify(
        Authority $authority,
        string $jws,
        bool $expectValid,
        ?string $expectedException = null
    ): void
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $this->assertEquals($expectValid, $authority->verify($jws, $expectedException !== null));
    }

    /**
     * @return mixed[][] The dataset for {@see testCheck}.
     */
    public function createCheckDataset(): array
    {
        $headers = base64_encode('{"alg":"HS256"}');
        $valid   = base64_encode('{"iss":"foo","sub":"42"}');
        $invalid = base64_encode('{"sub":"42"}');

        return [
            ["$headers.$valid.", true],
            ["$headers.$invalid.", false],
            ["$headers.$invalid.", false, InvalidClaimException::class],
        ];
    }

    /**
     * @dataProvider createCheckDataset
     * @covers ::check
     * @covers \Windy\Guardian\Exceptions\InvalidClaimException
     *
     * @param string      $jws               The JWS to check.
     * @param bool        $expectValid       If the JWS is expected to be valid.
     * @param string|null $expectedException The expected exception, if any.
     */
    public function testCheck(
        string $jws,
        bool $expectValid,
        ?string $expectedException = null
    ): void
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $this->assertEquals(
            $expectValid,
            $this->getAuthority()->check($jws, $expectedException !== null)
        );
    }
}
