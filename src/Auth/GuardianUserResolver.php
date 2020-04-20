<?php

declare(strict_types=1);

namespace Windy\Guardian\Auth;

use Illuminate\Auth\GenericUser;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\Authority;
use function array_key_exists;
use function json_decode;

/**
 * Retrieve the user based on the "sub" in a JWT token.
 */
class GuardianUserResolver
{
    /** @var Container $container The application container instance. */
    private $container;
    /** @var Repository $config The application configuration repository. */
    private $config;
    /** @var Authority $authority The authority to use. */
    private $authority;

    /**
     * @param Container $container The application container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the serialized JWT from the request.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return string The serialized JWT.
     */
    private function getToken(Request $request): string
    {
        return $request->bearerToken();
    }

    /**
     * Get the user based on the token "sub" claim value.
     *
     * @param string $sub The JWT "sub" claim value.
     *
     * @return Authenticatable|null The user if found, null otherwise.
     *
     * @throws BindingResolutionException
     */
    private function getUser(string $sub): ?Authenticatable
    {
        $strategy = $this->config->get('guardian.auth.strategy');
        $user     = null;

        if ($strategy === 'eloquent') {
            /** @var string $class The model class. */
            $class = $this->config->get('guardian.auth.eloquent.model');
            /** @var Model $model The model instance. */
            $model = new $class();
            $key   = $this->config->get('guardian.auth.eloquent.identifier') ?? $model->getKeyName();

            $user = $model->newQuery()->where($key, '=', $sub)->first();
        } elseif ($strategy === 'database') {
            /** @var DatabaseManager $manager */
            $manager    = $this->container->make('db');
            $connection = $manager->connection($this->config->get('guardian.auth.database.connection'));

            $data = $connection->table($this->config->get('guardian.auth.database.table', 'users'))
                ->where($this->config->get('guardian.auth.database.identifier', 'id'), '=', $sub)
                ->first();

            if ($data !== null) {
                $user = new GenericUser((array)$data);
            }
        }

        return $user;
    }

    /**
     * @param Request $request The Illuminate HTTP request.
     *
     * @return Model|Authenticatable|null The identified user, if any.
     *
     * @throws BindingResolutionException
     */
    public function __invoke(Request $request)
    {
        // Initialize dependencies at the last moment
        $this->config = $this->container->make('config');
        /** @var AuthoritiesRegistry $authorities */
        $authorities     = $this->container->make(AuthoritiesRegistry::class);
        $this->authority = $authorities->get($this->config->get('guardian.auth.authority'));

        $token = $this->getToken($request);

        if ($token === null) {
            // No token => no user authentication
            return null;
        }

        $this->authority->verify($token, true); // Verify signature
        $this->authority->check($token, true); // Validate claims

        // Token is now valid
        $payload = $this->authority->unserialize($token)->getPayload();
        $claims  = json_decode($payload, true, 512);

        if (!array_key_exists('sub', $claims)) {
            // No sub claim => no user authentication
            return null;
        }

        return $this->getUser($claims['sub']);
    }
}
