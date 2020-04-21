<?php

declare(strict_types=1);

namespace Windy\Guardian;

use Illuminate\Support\Facades\Facade;
use Jose\Component\Signature\JWS;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\Authority;

/**
 * Facade for {@see Authority}.
 *
 * @method static Authority get(string $name)
 * @method static array payload($payload)
 * @method static JWS|string sign($payload, bool $serialize = true)
 * @method static JWS unserialize($jws)
 * @method static bool verify($jws, bool $throw = false)
 * @method static bool check($jws, bool $throw = false)
 */
class Guardian extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuthoritiesRegistry::class;
    }
}
