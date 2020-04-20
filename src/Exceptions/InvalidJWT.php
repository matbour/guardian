<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class InvalidJWT extends UnauthorizedHttpException
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message = $message ?? 'Invalid JWT';

        parent::__construct('Bearer', $message, $previous, 0, []);
    }
}
