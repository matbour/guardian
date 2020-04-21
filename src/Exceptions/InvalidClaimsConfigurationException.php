<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use Throwable;

/**
 * Thrown a claims set does not exist.
 */
class InvalidClaimsConfigurationException extends InvalidConfigurationException
{
    public function __construct(string $claims, ?Throwable $previous = null)
    {
        $message = <<<PAYLOAD
Unknown claims configuration `$claims`. Did you forget to define it in your config/guardian.php? For instance:
config/guardian.php
[
    ...
    'claims' => [
        '$claims' => [
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

        parent::__construct($message, 0, $previous);
    }
}
