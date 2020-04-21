<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use Throwable;

/**
 * Thrown when the application tries to use a feature which is provided by an optional library.
 */
class MissingLibraryException extends GuardianException
{
    public function __construct(
        string $library,
        ?string $reason = null,
        ?Throwable $previous = null
    )
    {
        $reason = $reason ?? 'this feature';

        $message = "In order to use $reason, you need to install the package $library."
            . "You can do it by running: `composer require $library`";

        parent::__construct($message, 0, $previous);
    }
}
