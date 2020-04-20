<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Crypto;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory as JoseJWKFactory;
use Jose\Component\Signature\Algorithm\RS512;
use Mathrix\Lumen\JWT\Constants;
use Mathrix\Lumen\JWT\Exceptions\InvalidConfiguration;
use Mathrix\Lumen\JWT\Exceptions\MissingLibrary;
use Mathrix\Lumen\JWT\Utils\IO;
use function array_merge;
use function assert;
use function class_exists;
use function file_exists;
use function in_array;
use function json_encode;
use function storage_path;
use const JSON_PRETTY_PRINT;

/**
 * Create the {@see JWK} objects.
 */
class KeyFactory
{
    /** @var Factory $validator The Illuminate validator factory. */
    private $validator;

    /**
     * @param Container $container The application container.
     *
     * @throws BindingResolutionException
     */
    public function __construct(Container $container)
    {
        $this->validator = $container->make('validator');
    }

    /**
     * Get the default configuration.
     *
     * @return mixed[]
     */
    public function getDefaultConfig(): array
    {
        return [
            'algorithm' => RS512::class,
            'path'      => storage_path('jwt.json'),
        ];
    }

    /**
     * Sanitize the algorithm form the configuration.
     *
     * @param string $algorithm The algorithm from the config
     *
     * @return string The algorithm class full qualified name.
     *
     * @throws InvalidConfiguration
     * @throws MissingLibrary
     */
    public function getAlgorithm(string $algorithm): string
    {
        if (!Str::startsWith($algorithm, Constants::ALGORITHMS_NAMESPACE)) {
            $algorithm = Constants::ALGORITHMS_NAMESPACE . '\\' . Str::upper($algorithm);
        }

        if (class_exists($algorithm)) {
            return $algorithm;
        }

        // At this point, failure is expected, try to helper the final developer
        // @codeCoverageIgnoreStart
        if (in_array($algorithm, Constants::ECDSA_ALGORITHMS, true)) {
            throw new MissingLibrary('web-token/jwt-signature-algorithm-ecdsa', $algorithm);
        }

        if (in_array($algorithm, Constants::EDDSA_ALGORITHMS, true)) {
            throw new MissingLibrary('web-token/jwt-signature-algorithm-eddsa', $algorithm);
        }

        if (in_array($algorithm, Constants::HMAC_ALGORITHMS, true)) {
            throw new MissingLibrary('web-token/jwt-signature-algorithm-hmac', $algorithm);
        }

        if (in_array($algorithm, Constants::RSA_ALGORITHMS, true)) {
            throw new MissingLibrary('web-token/jwt-signature-algorithm-rsa', $algorithm);
        }
        // @codeCoverageIgnoreEnd

        throw InvalidConfiguration::algorithm($algorithm); // The algorithm is 100% invalid
    }

    /**
     * Create a {@see JWK} object using the provided configuration. If the key file already exists, simply use it.
     *
     * @param mixed[] $config The key configuration.
     *
     * @return Key The key object.
     *
     * @throws InvalidConfiguration
     * @throws MissingLibrary
     * @throws ValidationException
     */
    public function createFromConfig(array $config): Key
    {
        $config = array_merge($this->getDefaultConfig(), $config);
        $config = array_merge($config, ['algorithm' => $this->getAlgorithm($config['algorithm'])]);

        $algorithm = $config['algorithm'];
        $name      = (new $algorithm())->name();

        if (file_exists($config['path'])) {
            return new Key(
                JoseJWKFactory::createFromJsonObject(IO::read($config['path'])),
                $config
            );
        }

        $octalRule = static function (string $attribute, $value, Closure $fail) use ($name): void {
            if ($value % 8 === 0) {
                return;
            }

            $fail("$name key size must be a multiple of 8, but got $value.");
        };

        if (in_array($algorithm, Constants::ECDSA_ALGORITHMS, true)) {
            $config['curve'] = $config['curve'] ?? Constants::CURVE_P256;
            $this->validator
                ->make($config, [
                    'curve' => ['required', Rule::in(Constants::ECDSA_CURVES)],
                ])
                ->validate();

            $jwk = JoseJWKFactory::createECKey($config['curve']);
        } elseif (in_array($algorithm, Constants::EDDSA_ALGORITHMS, true)) {
            $config['curve'] = $config['curve'] ?? Constants::CURVE_ED25519;
            $this->validator
                ->make($config, [
                    'curve' => ['required', Rule::in(Constants::EDDSA_CURVES)],
                ])
                ->validate();

            $jwk = JoseJWKFactory::createOKPKey($config['curve']);
        } elseif (in_array($algorithm, Constants::HMAC_ALGORITHMS, true)) {
            $config['size'] = $config['size'] ?? Constants::HMAC_SIZES[$algorithm];
            $minSize        = Constants::HMAC_SIZES[$algorithm];
            $this->validator
                ->make(
                    $config,
                    [
                        'size' => ['required', $octalRule, "gte:$minSize"],
                    ],
                    [
                        'size.gte' => "HMAC key size must at least {$minSize} bits while using {$name}, but got "
                            . $config['size'],
                    ]
                )
                ->validate();

            $jwk = JoseJWKFactory::createOctKey($config['size']);
        } elseif (in_array($algorithm, Constants::RSA_ALGORITHMS, true)) {
            $config['size'] = $config['size'] ?? Constants::RSA_SIZES[$algorithm];
            $minSize        = Constants::RSA_SIZES[$algorithm];
            $this->validator
                ->make(
                    $config,
                    [
                        'size' => ['required', $octalRule, "gte:$minSize"],
                    ],
                    [
                        'size.gte' => "HMAC key size must at least {$minSize} bits while using {$name}, but got "
                            . $config['size'],
                    ]
                )
                ->validate();

            $jwk = JoseJWKFactory::createRSAKey($config['size']);
        }

        assert($jwk instanceof JWK);

        IO::write($config['path'], json_encode($jwk->jsonSerialize(), JSON_PRETTY_PRINT));

        return new Key($jwk, $config);
    }
}
