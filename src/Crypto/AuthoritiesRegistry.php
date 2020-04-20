<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Crypto;

use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;
use Mathrix\Lumen\JWT\Exceptions\InvalidConfiguration;
use Throwable;

class AuthoritiesRegistry extends Registry
{
    private $keyRegistry;
    private $claimsRegistry;

    public function __construct(
        Container $container,
        KeyRegistry $keyRegistry,
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
     * @throws ValidationException
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
     * @return Throwable The invalid configuration exception.
     */
    public function unknown(string $name): Throwable
    {
        return InvalidConfiguration::authority($name);
    }
}
