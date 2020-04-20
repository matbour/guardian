<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT;

use Jose\Component\Signature\Algorithm\EdDSA;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS384;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\Algorithm\PS384;
use Jose\Component\Signature\Algorithm\PS512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;

/**
 * Provide the library-wide constants declarations.
 *
 * phpcs:disable SlevomatCodingStandard.Classes.ConstantSpacing.IncorrectCountOfBlankLinesAfterConstant
 */
class Constants
{
    public const ALGORITHMS_NAMESPACE = 'Jose\\Component\\Signature\\Algorithm';

    // EDCSA constants
    public const ECDSA_ALGORITHMS = [ES256::class, ES384::class, ES512::class];
    public const CURVE_P256       = 'P-256';
    public const CURVE_P384       = 'P-384';
    public const CURVE_P521       = 'P-521';
    public const ECDSA_CURVES     = [self::CURVE_P256, self::CURVE_P384, self::CURVE_P521];

    // EDDSA constants
    public const EDDSA_ALGORITHMS = [EdDSA::class];
    public const CURVE_ED25519    = 'Ed25519';
    public const EDDSA_CURVES     = [self::CURVE_ED25519];

    // HMAC constants
    public const HMAC_ALGORITHMS = [HS256::class, HS384::class, HS512::class];
    public const HMAC_SIZES      = [
        HS256::class => 256,
        HS384::class => 384,
        HS512::class => 512,
    ];

    // RSA constants
    public const RSA_ALGORITHMS = [
        RS256::class,
        RS384::class,
        RS512::class,
        PS256::class,
        PS384::class,
        PS512::class,
    ];
    public const RSA_SIZES      = [
        RS256::class => 2048,
        RS384::class => 3072,
        RS512::class => 4096,
        PS256::class => 2048,
        PS384::class => 3072,
        PS512::class => 4096,
    ];
}
