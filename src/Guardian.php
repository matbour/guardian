<?php

declare(strict_types=1);

namespace Windy\Guardian;

use Illuminate\Support\Facades\Facade;
use Windy\Guardian\Crypto\AuthoritiesRegistry;

/**
 * @mixin AuthoritiesRegistry
 */
class Guardian extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuthoritiesRegistry::class;
    }
}
