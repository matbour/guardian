<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Crypto;

use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\HS256;
use Windy\Guardian\Crypto\Authority;
use Windy\Guardian\Crypto\Claims;
use Windy\Guardian\Crypto\Key;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Crypto\Claims
 */
class ClaimsTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     * @covers ::setChecker
     * @covers ::generate
     * @covers ::check
     */
    public function testCheckValidSignedToken(): void
    {
        $key    = new Key(JWKFactory::createOctKey(256), ['algorithm' => HS256::class]);
        $claims = new Claims([
            'iss' => 'Issuer',
            'aud' => 'Audience',
            'exp' => '+3 days',
            'nbf' => 'now',
            'iat' => 'now',
            'jid' => 'uuid',
        ]);

        $authority = new Authority($key, $claims);
        $jws       = $authority->sign($claims->generate(), false);

        $this->assertTrue($claims->check($jws, $key));
    }
}
