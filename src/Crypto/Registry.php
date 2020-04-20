<?php

declare(strict_types=1);

namespace Windy\Guardian\Crypto;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use function array_key_exists;

/**
 * Base class for the library registries.
 */
abstract class Registry
{
    /** @var string The configuration key. */
    protected $key;
    /** @var mixed[] $cache The loaded registry objects. */
    protected $cache = [];
    /** @var Container $container The application container instance. */
    protected $container;
    /** @var Repository $config The application configuration. */
    protected $config;

    /**
     * @param Container $container The application container instance.
     * @param string    $key       The registry key in the JWT configuration.
     *
     * @throws BindingResolutionException
     */
    public function __construct(Container $container, string $key)
    {
        $this->key    = $key;
        $this->config = $container->make('config');
    }

    /**
     * Check if a key exists in the registry.
     *
     * @param string $name The key name.
     *
     * @return bool If the key configuration exists.
     */
    public function exists(string $name): bool
    {
        return $this->config->has('guardian.' . Str::plural($this->key) . ".$name");
    }

    /**
     * Get the default object.
     *
     * @return mixed The object associated with the default object configuration.
     *
     * @throws ValidationException
     */
    public function default()
    {
        return $this->get($this->config->get("guardian.defaults.{$this->key}", 'default'));
    }

    /**
     * @param mixed[] $config The object configuration.
     *
     * @return mixed The newly created object.
     */
    abstract public function create(array $config);

    /**
     * Handle known configuration errors.
     *
     * @param string $name The config name which does not exist.
     *
     * @return Throwable The exception to throw.
     */
    abstract public function unknown(string $name): Throwable;

    /**
     * @param string|null $name The object name. If null, use the default object name.
     *
     * @return mixed The object associated with the provided object configuration.
     *
     * @throws ValidationException
     */
    public function get(?string $name = null)
    {
        if ($name === null) {
            return $this->default();
        }

        if ($this->exists($name)) {
            if (array_key_exists($name, $this->cache)) {
                return $this->cache[$name];
            }

            $this->cache[$name] = $this->create(
                $this->config->get('guardian.' . Str::plural($this->key) . ".$name", [])
            );

            return $this->cache[$name];
        }

        throw $this->unknown($name);
    }

    /**
     * Dynamically call the default instance.
     *
     * @param string  $method     The method to call.
     * @param mixed[] $parameters The method parameters.
     *
     * @return mixed
     *
     * @throws ValidationException
     */
    public function __call(string $method, array $parameters)
    {
        return $this->default()->$method(...$parameters);
    }
}
