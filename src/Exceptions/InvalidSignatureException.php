<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use Throwable;

/**
 * Thrown when the signature of a JWT is invalid.
 */
class InvalidSignatureException extends GuardianException
{
    public function __construct(
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message ?? 'Invalid signature', $code, $previous);
    }
}
