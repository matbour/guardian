<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use Throwable;

/**
 * Thrown an authority does not exist.
 */
class InvalidAuthorityConfigurationException extends InvalidConfigurationException
{
    public function __construct(string $name, ?Throwable $previous = null)
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

        parent::__construct($message, 0, $previous);
    }
}
