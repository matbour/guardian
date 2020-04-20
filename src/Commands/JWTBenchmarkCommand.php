<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Commands;

use Illuminate\Console\Command;
use Jose\Component\Signature\Algorithm\EdDSA;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS384;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\Algorithm\PS384;
use Jose\Component\Signature\Algorithm\PS512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Mathrix\Lumen\JWT\Drivers\ECDSADriver;
use Mathrix\Lumen\JWT\Drivers\EdDSADriver;
use Mathrix\Lumen\JWT\Drivers\HMACDriver;
use Mathrix\Lumen\JWT\Drivers\RSADriver;
use function class_exists;
use function is_numeric;
use function microtime;
use function round;

/**
 * Benchmark the signature and verification algorithms.
 */
class JWTBenchmarkCommand extends Command
{
    protected $signature   = 'jwt:benchmark {--iterations=100 : Number of iterations to run}';
    protected $description = 'Benchmark the signature and verification algorithms';

    private const CONFIGS = [
        ECDSADriver::class => [
            [ES256::class, ECDSADriver::CURVE_P256],
            [ES384::class, ECDSADriver::CURVE_P384],
            [ES512::class, ECDSADriver::CURVE_P521],
        ],
        EdDSADriver::class => [
            [EdDSA::class, EdDSADriver::CURVE_ED25519],
        ],
        HMACDriver::class  => [
            [HS256::class, '256'],
            [HS384::class, '384'],
            [HS512::class, '512'],
        ],
        RSADriver::class   => [
            [RS256::class, '2048'],
            [RS384::class, '3072'],
            [RS512::class, '4096'],
            [PS256::class, '2048'],
            [PS384::class, '3072'],
            [PS512::class, '4096'],
        ],
    ];

    /** @var array $results */
    private $results = [];

    public function handle(): void
    {
        $iterations = (int)$this->option('iterations');

        $this->info("Benchmarking JWT signatures and verifications using <info>$iterations</info> iterations");

        foreach (self::CONFIGS as $driverClass => $configs) {
            $name    = $driverClass::NAME;
            $library = $driverClass::LIBRARY;

            if (!class_exists($driverClass::ALGORITHMS[0])) {
                $this->line("> Skipping <info>$name</info> because it requires $library");
                continue;
            }

            $this->line("> Benchmarking <info>$name</info>");

            foreach ($configs as [$algorithm, $curveOrSize]) {
                $driver = $this->getDriver($algorithm, $curveOrSize);

                if ($driver === null) {
                    continue;
                }

                $this->line("  + Using algorithm <info>{$driver->getAlgorithmName()}</info> ({$curveOrSize})");
                $this->benchmark($this->getDriver($algorithm, $curveOrSize), $curveOrSize, $iterations);
            }
        }

        $this->table([
            'Algorithm',
            'Curve / Size',
            'Signature (µs)',
            'Signatures / sec',
            'Verification (µs)',
            'Verifications / sec',
        ], $this->results, 'box-double');
    }

    /**
     * Get the driver from the algorithm.
     *
     * @param string $algorithm   The algorithm class.
     * @param string $curveOrSize The curve (for ECDSA and EdDSA) or the key size in bits (HMAC and RSA).
     *
     * @return Driver|null
     */
    private function getDriver(string $algorithm, string $curveOrSize): ?Driver
    {
        if (is_numeric($curveOrSize)) {
            // Bit-sized drivers
            return Driver::from([
                'algorithm' => $algorithm,
                'size'      => (int)$curveOrSize,
            ]);
        }

        return Driver::from([
            'algorithm' => $algorithm,
            'curve'     => $curveOrSize,
        ]);
    }

    /**
     * Run a signature and verification benchmark for a given driver.
     *
     * @param Driver $driver      The driver to use for signature and verification.
     * @param string $curveOrSize The curve (for ECDSA and EdDSA) or the key size in bits (HMAC and RSA).
     * @param int    $iterations  The number of iterations.
     */
    private function benchmark(?Driver $driver, string $curveOrSize, int $iterations): void
    {
        if ($driver === null) {
            // Do not run benchmark if driver is null.
            return;
        }

        $payloads = [];
        $tokens   = [];

        // Generate the payloads (does not count for timing measures)
        for ($i = 0; $i < $iterations; $i++) {
            $payloads[] = ['sub' => $i + 1];
        }

        // Signature benchmark start
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $tokens[] = $driver->sign($payloads[$i]);
        }

        $inter = microtime(true); // Signature benchmark end / Verification benchmark start

        for ($i = 0; $i < $iterations; $i++) {
            $driver->verify($tokens[$i]);
        }

        $end = microtime(true); // Verification benchmark end

        $this->results[] = [
            'algorithm'   => $driver->getAlgorithmName(),
            'curveOrSize' => $curveOrSize,
            'sign_time'   => round((($inter - $start) / $iterations) * 1000 * 1000, 1), // µs
            'sign_freq'   => round($iterations / ($inter - $start), 1),
            'verif_time'  => round((($end - $inter) / $iterations) * 1000 * 1000, 1), // µs
            'verif_freq'  => round($iterations / ($end - $inter), 1),
        ];
    }
}
