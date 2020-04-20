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
use Mathrix\Lumen\JWT\Utils\JWTConfig;
use function file_exists;
use function storage_path;
use function unlink;

/**
 * Generate a new JWT key. By default, it will use the config/jwt.php configuration file.
 */
class JWTKeyCommand extends Command
{
    protected $signature = 'jwt:key '
    . '{--f|force : Force the key creation, even if a key already exist} '
    . '{--a|algorithm= : The algorithm} '
    . '{--c|curve= : EdDSA/ES* only: the elliptic curve used to generate the key} '
    . '{--s|size= : HS*/RS*/PS* only: the key size in bits} '
    . '{--p|path= : The key path, relative to storage directory} ';

    public function handle(): int
    {
        $force  = $this->option('force') !== false;
        $config = JWTConfig::key();

        $config['algorithm'] = $this->option('algorithm') ?? $config['algorithm'];
        $config['path']      = $this->option('path') ?? $config['path'] ?? storage_path('jwt_key.json');

        switch ($config['algorithm']) {
            case ES256::class:
            case ES384::class:
            case EdDSA::class:
            case ES512::class:
                $config['curve'] = $this->option('curve') ?? $config['curve'];
                break;
            case HS256::class:
            case HS384::class:
            case HS512::class:
            case PS512::class:
            case PS384::class:
            case PS256::class:
            case RS512::class:
            case RS384::class:
            case RS256::class:
                $config['size'] = (int)($this->option('size') ?? $config['size']);
                break;
        }

        // Write the key
        if (!$force && file_exists($config['path'])) {
            $this->error("A key already exists at {$config['path']}, ignoring");

            return 1;
        }

        // When forcing creation of the key, remove it if it exists
        if ($force && file_exists($config['path'])) {
            unlink($config['path']);
        }

        $driver = Driver::from($config, []);
        $driver->getPublicJWK();
        $this->line("Generated a new key in {$config['path']}");

        return 0;
    }
}
