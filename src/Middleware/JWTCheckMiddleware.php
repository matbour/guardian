<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Mathrix\Lumen\JWT\Drivers\Driver;

class JWTCheckMiddleware
{
    public const NAME = 'jwt.check';
    /** @var Driver $driver */
    private $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws InvalidClaimException
     * @throws MissingMandatoryClaimException
     */
    public function handle(Request $request, Closure $next)
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken !== null) {
            $this->driver->check($bearerToken);
        }

        return $next($request);
    }
}
