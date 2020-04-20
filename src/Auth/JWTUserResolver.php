<?php

declare(strict_types=1);

namespace Mathrix\Lumen\JWT\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mathrix\Lumen\JWT\Drivers\Driver;
use function data_get;
use function json_decode;

/**
 * Retrieve the user based on the 'sub' in a JWT token.
 */
class JWTUserResolver
{
    /**
     * @param Request $request The Illuminate HTTP request.
     *
     * @return Model|Authenticatable|null The identified user, if any.
     *
     * @throws BindingResolutionException
     */
    public function __invoke(Request $request)
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken === null) {
            // No token => no user authentication
            return null;
        }

        /** @var Driver $driver */
        $driver = Container::getInstance()->make(Driver::class);

        if (!$driver->verify($bearerToken)) {
            // Verification failed => no user authentication
            return null;
        }

        $payload = $driver->unserialize($bearerToken)->getPayload();
        $data    = json_decode($payload, true, 512);
        /** @var string $sub Get the "sub" claim. */
        $sub = data_get($data, 'sub');

        if ($sub === null) {
            // no sub claim => no user authentication
            return null;
        }

        $model = Container::getInstance()->make('config')->get('jwt.auth.user_model');

        /** @var Authenticatable $instance Only used to retrieve the auth identifier name. */
        $instance = new $model();

        /** @var Builder $builder */
        $builder = $model::query();

        return $builder->where($instance->getAuthIdentifierName(), '=', $sub)->first();
    }
}
