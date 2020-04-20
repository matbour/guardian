<?php

declare(strict_types=1);

namespace Windy\Guardian\Exceptions;

use RuntimeException;

/**
 * Base class for the library exception. Useful to quickly identify exceptions thrown from the library with the
 * instanceof operator.
 */
class JWTException extends RuntimeException
{
}
