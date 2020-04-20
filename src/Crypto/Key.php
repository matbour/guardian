<?php

declare(strict_types=1);

namespace Windy\Guardian\Crypto;

use Jose\Component\Core\Algorithm;
use Jose\Component\Core\JWK;

class Key
{
    /** @var mixed[] The key configuration. */
    private $config;
    /** @var JWK The underlying JWK. */
    private $jwk;

    /**
     * @param JWK     $jwk    The JWK object.
     * @param mixed[] $config The key configuration.
     */
    public function __construct(JWK $jwk, array $config)
    {
        $this->config = $config;
        $this->jwk    = $jwk;
    }

    /**
     * Get the key algorithm.
     *
     * @return Algorithm the key algorithm.
     */
    public function getAlgorithm(): Algorithm
    {
        return new $this->config['algorithm']();
    }

    /**
     * Get the key thumbprint. Used in key integrity checks.
     *
     * @return string The key thumbprint, sha256-hashed.
     */
    public function getThumbprint(): string
    {
        return $this->jwk->thumbprint('sha256');
    }

    /**
     * Get the underlying JWK. Used in signature generation.
     *
     * @return JWK The underlying JWK.
     */
    public function getJWK(): JWK
    {
        return $this->jwk;
    }

    /**
     * Get the underlying JWK. Used in signature verification.
     *
     * @return JWK The underlying public JWK.
     */
    public function getPublicJWK(): JWK
    {
        return $this->jwk->toPublic();
    }
}
