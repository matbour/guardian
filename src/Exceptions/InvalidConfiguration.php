<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use InvalidArgumentException;
use Throwable;

class InvalidConfiguration extends InvalidArgumentException
{
    public static function algorithm(string $algorithm, ?Throwable $previous = null): self
    {
        return new self("Invalid algorithm $algorithm", 0, $previous);
    }

    public static function claim(string $claim, ?Throwable $previous = null): self
    {
        $message = "Invalid claim {$claim} configuration";

        if ($previous !== null) {
            $message .= ": {$previous->getMessage()}";
        }

        return new self($message, 0, $previous);
    }

    public static function key(string $name, ?Throwable $previous = null): self
    {
        $message = <<<PAYLOAD
Unknown key configuration `$name`. Did you forget to define it in your config/guardian.php? For instance:
config/guardian.php
[
    ...
    'keys' => [
        '$name' => [
            'algorithm' => 'HS512',
            'size'      => 1024,
            'path'      => storage_path('keychain/jwt_auth.json'),
        ]
    ]
    ...
]
PAYLOAD;

        return new self($message, 0, $previous);
    }

    public static function claims(string $name, ?Throwable $previous = null): self
    {
        $message = <<<PAYLOAD
Unknown payload configuration `$name`. Did you forget to define it in your config/guardian.php? For instance:
config/guardian.php
[
    ...
    'payloads' => [
        '$name' => [
            'iss' => 'Your Issuer',
            'aud' => 'Your Audience',
            'exp' => '+1 day',
            'nbf' => 'now',
            'iat' => 'now',
            'jid' => 'uuid',
        ]
    ]
    ...
]
PAYLOAD;

        return new self($message, 0, $previous);
    }

    public static function authority(string $name, ?Throwable $previous = null): self
    {
        $message = <<<PAYLOAD
Unknown key configuration `$name`. Did you forget to define it in your config/guardian.php? For instance:
config/guardian.php
[
    ...
    'authorities' => [
        '$name' => [
            'key'    => 'default',
            'claims' => 'default',
        ]
    ]
    ...
]
PAYLOAD;

        return new self($message, 0, $previous);
    }
}
