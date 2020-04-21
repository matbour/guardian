<?php

declare(strict_types=1);

namespace Windy\Guardian\Crypto;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Jose\Component\Checker\InvalidClaimException as JoseInvalidClaimException;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Windy\Guardian\Exceptions\InvalidClaimException;
use Windy\Guardian\Exceptions\InvalidSignatureException;
use Windy\Guardian\Exceptions\JWTException;
use Traversable;
use function array_merge;
use function is_resource;
use function is_scalar;
use function is_string;
use function iterator_to_array;
use function json_encode;
use function stream_get_contents;

class Authority
{
    /** @var Key $key The authority key. */
    private $key;
    /** @var Claims $claims The authority claims. */
    private $claims;
    /** @var JWSBuilder The JWT builder. */
    private $builder;
    /** @var JWSVerifier The JWT verifier. */
    private $verifier;
    /** @var CompactSerializer $serializer The JWT serializer. */
    private $serializer;

    public function __construct(Key $key, Claims $claims)
    {
        $manager = new AlgorithmManager([$key->getAlgorithm()]);

        $this->key        = $key;
        $this->claims     = $claims;
        $this->builder    = new JWSBuilder($manager);
        $this->verifier   = new JWSVerifier($manager);
        $this->serializer = new CompactSerializer();
    }

    /**
     * Prepare the payload.
     *
     * @param mixed $payload The input payload.
     *
     * @return mixed[] The output payload.
     */
    public function payload($payload): array
    {
        if (is_resource($payload)) {
            $payload = $this->payload(stream_get_contents($payload));
        } elseif ($payload instanceof Authenticatable) {
            // Login use case, use the "sub" claim
            $payload = ['sub' => $payload->getAuthIdentifier()];
        } elseif ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        } elseif ($payload instanceof Traversable) {
            $payload = iterator_to_array($payload);
        } elseif (is_scalar($payload)) {
            // Wrap scalars into the "data" claim
            $payload = ['data' => $payload];
        }

        // Generated claims have always the maximum priority for security reasons
        return array_merge($payload, $this->claims->generate());
    }

    /**
     * @param mixed $payload   The payload to sign.
     * @param bool  $serialize If the token should be serialized immediately.
     *
     * @return JWS|string
     */
    public function sign($payload, bool $serialize = true)
    {
        $payload       = $this->payload($payload);
        $payloadString = json_encode($payload, 0, 512);

        $jws = $this->builder->create()
            ->withPayload($payloadString)
            ->addSignature($this->key->getJWK(), [
                'typ' => 'JWT',
                'alg' => $this->key->getAlgorithm()->name(),
            ])
            ->build();

        return !$serialize ? $jws : $this->serializer->serialize($jws);
    }

    /**
     * @param JWS|string $jws The JWS or the serialized token to unserialize.
     *
     * @return JWS The unserialized JWS.
     */
    public function unserialize($jws): JWS
    {
        if (is_string($jws)) {
            $jws = $this->serializer->unserialize($jws);
        }

        return $jws;
    }

    /**
     * @param JWS|string $jws   The JWS or the serialized token to check.
     * @param bool       $throw If an exception should be thrown on failure instead of returning false.
     *
     * @return bool If the provided JWS signature is valid.
     */
    public function verify($jws, bool $throw = false): bool
    {
        $result = $this->verifier->verifyWithKey(
            $this->unserialize($jws),
            $this->key->getPublicJWK(),
            0
        );

        if (!$result && $throw) {
            throw new InvalidSignatureException();
        }

        return $result;
    }

    /**
     * @param JWS|string $jws   The JWS or the serialized token to check.
     * @param bool       $throw Bubble the exception on failure instead of returning false.
     *
     * @return bool If the provided JWS is valid.
     *
     * @throws InvalidClaimException
     */
    public function check($jws, bool $throw = false): bool
    {
        $jws = $this->unserialize($jws);

        try {
            $result = $this->claims->check($jws, $this->key);
        } catch (JoseInvalidClaimException | MissingMandatoryClaimException $e) {
            if ($throw) {
                throw new InvalidClaimException($e->getMessage(), $e->getCode(), $e);
            }

            return false;
        }

        return $result;
    }
}
