<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use Throwable;

/**
 * Thrown when a authentication guard using the guardian driver is not properly configured.
 */
class InvalidGuardConfigurationException extends InvalidConfigurationException
{
    public function __construct(string $guard, ?Throwable $previous = null)
    {
        $message = <<<PAYLOAD
Missing user provider in the guard `$guard`. Did you forget to define it in your config/auth.php? For instance:
config/auth.php
[
    ...
    'guards'    => [
        ...
        '$guard' => [
            'driver'    => 'guardian',
            'provider'  => 'users', // <- Here!
            'authority' => 'default',
        ],
        ...
    ],
    ...
]
PAYLOAD;

        parent::__construct($message, 0, $previous);
    }
}
