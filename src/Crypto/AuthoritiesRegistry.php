<?php

declare(strict_types=1);

namespace Windy\Guardian\Crypto;

use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;
use Windy\Guardian\Exceptions\InvalidConfiguration;
use Throwable;

/**
 * @mixin Authority
 */
class AuthoritiesRegistry extends Registry
{
    private $keyRegistry;
    private $claimsRegistry;

    public function __construct(
        Container $container,
        KeysRegistry $keyRegistry,
        ClaimsRegistry $claimsRegistry
    )
    {
        parent::__construct($container, 'authorities');

        $this->keyRegistry    = $keyRegistry;
        $this->claimsRegistry = $claimsRegistry;
    }

    /**
     * @param mixed[] $config The authority configuration.
     *
     * @return Authority The newly created authority.
     *
     * @throws InvalidConfiguration
     */
    public function create(array $config): Authority
    {
        return new Authority(
            $this->keyRegistry->get($config['key']),
            $this->claimsRegistry->get($config['claims'])
        );
    }

    /**
     * @param string $name The key configuration name.
     *
     * @return InvalidConfiguration
     */
    public function unknown(string $name): Throwable
    {
        return InvalidConfiguration::authority($name);
    }
}
