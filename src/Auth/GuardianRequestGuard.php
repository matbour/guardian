<?php

declare(strict_types=1);

namespace Windy\Guardian\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Throwable;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use function array_key_exists;
use function json_decode;

/**
 * Retrieve the user based on the "sub" in a JWT token.
 */
class GuardianRequestGuard implements Guard
{
    use GuardHelpers;

    /** @var AuthoritiesRegistry $authorities */
    private $authorities;
    /** @var Repository $config */
    private $config;
    /** @var Request $request */
    private $request;

    /**
     * @param AuthoritiesRegistry $authorities The loaded authorities.
     * @param mixed[]             $config      The guard configuration.
     * @param Request             $request     The current request.
     * @param UserProvider        $provider    The current user provider.
     */
    public function __construct(
        AuthoritiesRegistry $authorities,
        array $config,
        Request $request,
        UserProvider $provider
    )
    {
        $this->authorities = $authorities;
        $this->config      = new Repository($config);
        $this->request     = $request;
        $this->provider    = $provider;
    }

    /**
     * @param mixed[] $credentials The user credentials.
     *
     * @return bool If the user should be authenticated.
     *
     * @throws Throwable
     */
    public function validate(array $credentials = []): bool
    {
        return (clone $this)->setRequest($credentials['request'])->user() !== null;
    }

    /**
     * Set the current request instance. Used to keep the request attribute in sync.
     *
     * @param Request $request The new request instance.
     *
     * @return $this
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return Authenticatable|null The identified user, if any.
     *
     * @throws Throwable
     */
    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->request->bearerToken();

        if ($token === null) {
            // No token => no user authentication
            return null;
        }

        $authority = $this->authorities->get($this->config->get('authority'));
        $authority->verify($token, true); // Verify signature
        $authority->check($token, true); // Validate claims

        // Token is now valid
        $payload = $authority->unserialize($token)->getPayload();
        $claims  = json_decode($payload, true, 512);

        if (!array_key_exists('sub', $claims)) {
            // No sub claim => no user authentication
            return null;
        }

        return $this->user = $this->provider->retrieveById($claims['sub']);
    }
}
