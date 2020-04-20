<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Exceptions;

use Throwable;

class InvalidSignatureException extends JWTException
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
