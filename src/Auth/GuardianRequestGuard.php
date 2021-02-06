<?php

declare(strict_types=1);

namespace Windy\Guardian\Auth;

use Illuminate\Auth\AuthManager;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Throwable;
use Windy\Guardian\Crypto\AuthoritiesRegistry;
use Windy\Guardian\Crypto\Authority;
use Windy\Guardian\Exceptions\InvalidGuardConfigurationException;
use function array_key_exists;
use function count;
use function json_decode;

/**
 * Retrieve the user based on the "sub" in a JWT token.
 */
class GuardianRequestGuard implements StatefulGuard
{
    use GuardHelpers;

    /** @var Container $container */
    protected $container;
    /** @var Repository $config */
    protected $config;
    /** @var Authority $authority */
    protected $authority;
    /** @var Authenticatable $lastAttempted */
    protected $lastAttempted;

    /**
     * @param Container $container The application container.
     * @param string    $guard     The authentication guard name.
     * @param mixed[]   $config    The guard configuration.
     */
    public function __construct(Container $container, string $guard, array $config)
    {
        $this->container = $container;
        $this->config    = new Repository($config);

        if (!array_key_exists('provider', $config) || !array_key_exists('authority', $config)) {
            throw new InvalidGuardConfigurationException($guard);
        }
    }

    /**
     * Get the guard user provider.
     *
     * @throws BindingResolutionException
     */
    public function getProvider(): UserProvider
    {
        if (!$this->provider) {
            /** @var AuthManager $auth */
            $auth           = $this->container->make('auth');
            $this->provider = $auth->createUserProvider($this->config->get('provider'));
        }

        return $this->provider;
    }

    /**
     * Get the guard authority.
     *
     * @return Authority The guard authority.
     *
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function getAuthority(): Authority
    {
        if (!$this->authority) {
            /** @var AuthoritiesRegistry $registry */
            $registry        = $this->container->make(AuthoritiesRegistry::class);
            $this->authority = $registry->get($this->config->get('authority'));
        }

        return $this->authority;
    }

    /**
     * Get the current request.
     *
     * @return Request Il Illuminate Http request.
     *
     * @throws BindingResolutionException
     */
    public function getRequest(): Request
    {
        return $this->container->make('request');
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

        if ($this->attempt()) {
            return $this->user;
        }

        return null;
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
        if (array_key_exists('request', $credentials) || count($credentials) === 0) {
            return $this->validateRequest($credentials['request'] ?? $this->getRequest());
        }

        return $this->validateCredentials($credentials);
    }

    /**
     * Validate the user credentials through a request.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return bool If the user was validated through the request.
     *
     * @throws BindingResolutionException
     * @throws Throwable
     */
    protected function validateRequest(Request $request): bool
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return false;
        }

        $this->getAuthority()->verify($token, true); // Verify signature
        $this->getAuthority()->check($token, true); // Validate claims

        // Token is now valid
        $payload = $this->getAuthority()->unserialize($token)->getPayload();
        $claims  = json_decode($payload, true, 512);

        if (!array_key_exists('sub', $claims)) {
            // No sub claim => no user authentication
            return false;
        }

        return ($this->lastAttempted = $this->getProvider()->retrieveById(
            $claims['sub']
        )) !== false;
    }

    /**
     * Validate the user credentials through credentials.
     *
     * @param mixed[] $credentials The user credentials.
     *
     * @return bool If the user was validated through credentials.
     *
     * @throws BindingResolutionException
     */
    protected function validateCredentials(array $credentials): bool
    {
        $this->lastAttempted = $this->getProvider()->retrieveByCredentials($credentials);

        if ($this->lastAttempted === null) {
            return false;
        }

        return $this->getProvider()->validateCredentials($this->lastAttempted, $credentials);
    }

    /**
     * Log a user into the application.
     *
     * @param Authenticatable $user     The user.
     * @param bool            $remember Unused.
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function login(Authenticatable $user, $remember = false): void
    {
        $this->setUser($user);
    }

    /**
     * Log the given user ID into the application.
     *
     * @param int|string $id       The user ID.
     * @param bool       $remember Unused.
     *
     * @return Authenticatable|false
     *
     * @throws BindingResolutionException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function loginUsingId($id, $remember = false)
    {
        $this->login($this->getProvider()->retrieveById($id));

        return $this->user;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param mixed[] $credentials The user credentials (also allow request).
     * @param bool    $remember    Unused.
     *
     * @return bool If the authentication succeeded.
     *
     * @throws Throwable
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function attempt(array $credentials = [], $remember = false): bool
    {
        if ($this->validate($credentials)) {
            $this->login($this->lastAttempted);

            return true;
        }

        return false;
    }

    /**
     * Alias of {@see attempt}.
     * Log a user into the application without sessions or cookies.
     *
     * @param mixed[] $credentials The user credentials (also allow request).
     *
     * @return bool If the authentication succeeded.
     *
     * @throws Throwable
     */
    public function once(array $credentials = []): bool
    {
        return $this->attempt($credentials);
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param int|string $id The user ID.
     *
     * @return Authenticatable|bool The user on success, false otherwise.
     *
     * @throws BindingResolutionException
     */
    public function onceUsingId($id)
    {
        if ($this->loginUsingId($id) !== null) {
            return $this->user;
        }

        return false;
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool Always false.
     */
    public function viaRemember(): bool
    {
        return false;
    }

    /**
     * Log the user out of the application.
     */
    public function logout(): void
    {
        $this->user = null;
    }
}
