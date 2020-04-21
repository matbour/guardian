<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use Throwable;

/**
 * Thrown when a key does not exist.
 */
class InvalidKeyConfigurationException extends InvalidConfigurationException
{
    public function __construct(string $key, ?Throwable $previous = null)
    {
        $message = <<<PAYLOAD
Unknown key configuration `$key`. Did you forget to define it in your config/guardian.php? For instance:
config/guardian.php
[
    ...
    'keys' => [
        '$key' => [
            'algorithm' => 'HS512',
            'size'      => 1024,
            'path'      => storage_path('guardian.json'),
        ]
    ]
    ...
]
PAYLOAD;

        parent::__construct($message, 0, $previous);
    }
}
