<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Facades;

use Illuminate\Support\Facades\Facade;
use Mathrix\Lumen\JWT\Crypto\AuthoritiesRegistry;

/**
 * @mixin AuthoritiesRegistry
 */
class JWT extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuthoritiesRegistry::class;
    }
}
