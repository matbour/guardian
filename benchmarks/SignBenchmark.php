<?php

declare(strict_types=1);

namespace Windy\Guardian\Benchmarks;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Validation\ValidationException;
use Jose\Component\Signature\Algorithm\EdDSA;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use Windy\Guardian\Constants;
use Windy\Guardian\Crypto\Authority;
use Windy\Guardian\Crypto\Claims;
use Windy\Guardian\Crypto\KeyFactory;
use function class_basename;
use function dirname;
use function storage_path;
use function strtolower;

/**
 * @BeforeMethods({"createApplication"})
 */
class SignBenchmark
{
    /** @var LaravelApplication $app The laravel application. */
    private $app;
    /** @var KeyFactory $factory The key factory. */
    private $factory;

    /**
     * Create the Laravel application.
     */
    public function createApplication(): void
    {
        if (!$this->app) {
            $this->app = require dirname(__DIR__) . '/sandbox/bootstrap/bootstrap.laravel.php';
            $this->app->make(Kernel::class)->bootstrap();
            $this->factory = $this->app->make(KeyFactory::class);
        }
    }

    /**
     * @return mixed[][] The key configuration dataset.
     */
    public function createAuthoritiesDataset(): array
    {
        $this->createApplication();

        $configs = [];

        // ECDSA
        foreach (Constants::ECDSA_ALGORITHMS as $algorithm) {
            foreach (Constants::ECDSA_CURVES as $curve) {
                $name = strtolower('bench_' . class_basename($algorithm) . '_' . $curve);

                $configs[$name] = [
                    'config' => [
                        'algorithm' => $algorithm,
                        'curve'     => $curve,
                        'path'      => storage_path("$name.json"),
                    ],
                ];
            }
        }

        // EdDSA
        $configs['bench_eddsa_ed25519'] = [
            'config' => [
                'algorithm' => EdDSA::class,
                'curve'     => Constants::CURVE_ED25519,
                'path'      => storage_path('bench_eddsa_ed25519.json'),
            ],
        ];

        // HMAC
        foreach (Constants::HMAC_ALGORITHMS as $algorithm) {
            $size = Constants::HMAC_SIZES[$algorithm];
            $name = strtolower('bench_' . class_basename($algorithm) . '_' . $size);

            $configs[$name] = [
                'config' => [
                    'algorithm' => $algorithm,
                    'size'      => $size,
                    'path'      => storage_path("$name.json"),
                ],
            ];
        }

        // RSA
        foreach (Constants::RSA_ALGORITHMS as $algorithm) {
            $size = Constants::RSA_SIZES[$algorithm];
            $name = strtolower('bench_' . class_basename($algorithm) . '_' . $size);

            $configs[$name] = [
                'config' => [
                    'algorithm' => $algorithm,
                    'size'      => $size,
                    'path'      => storage_path("$name.json"),
                ],
            ];
        }

        return $configs;
    }

    /**
     * @param mixed[] $params The benchmark parameters (config).
     *
     * @throws ValidationException
     *
     * @ParamProviders({"createAuthoritiesDataset"})
     * @Warmup(10)
     * @Revs(100)
     */
    public function benchSign(array $params): void
    {
        $authority = new Authority(
            $this->factory->createFromConfig($params['config']),
            new Claims([])
        );
        $authority->sign(['sub' => 42]);
    }
}
