<?php

declare(strict_types=1);

namespace Windy\Guardian\Crypto;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Windy\Guardian\Exceptions\InvalidConfiguration;
use Throwable;

/**
 * Holds the claims declared in the JWT configuration.
 *
 * @mixin Claims
 */
class ClaimsRegistry extends Registry
{
    /**
     * @param Container $container The application container instance.
     *
     * @throws BindingResolutionException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container, 'claims');
    }

    /**
     * @param mixed[] $config The claims configuration.
     *
     * @return Claims The newly created claims.
     */
    public function create(array $config): Claims
    {
        return new Claims($config);
    }

    /**
     * @param string $name The unknown claims configuration name.
     *
     * @return Throwable The invalid configuration exception.
     */
    public function unknown(string $name): Throwable
    {
        return InvalidConfiguration::claims($name);
    }
}
