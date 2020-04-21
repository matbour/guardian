<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use Throwable;

/**
 * Thrown when a algorithm declaration is invalid.
 */
class InvalidAlgorithmExceptionException extends InvalidConfigurationException
{
    public function __construct(string $algorithm, ?Throwable $previous = null)
    {
        parent::__construct("Invalid algorithm $algorithm", 0, $previous);
    }
}
