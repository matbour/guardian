<?php

declare(strict_types=1);

namespace Windy\Guardian\Crypto;

use Illuminate\Contracts\Container\Container;
use Throwable;
use Windy\Guardian\Exceptions\InvalidAuthorityConfigurationException;
use Windy\Guardian\Exceptions\InvalidConfigurationException;

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
     * @throws InvalidConfigurationException
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
     * @return InvalidAuthorityConfigurationException The invalid authority configuration exception.
     */
    public function unknown(string $name): Throwable
    {
        return new InvalidAuthorityConfigurationException($name);
    }
}
