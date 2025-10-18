<?php

namespace Payavel\Orchestration;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Traits\SimulatesAttributes;
use RuntimeException;

class ServiceConfig
{
    use SimulatesAttributes;

    /**
     * The service id.
     *
     * @var string|int
     */
    protected string|int $id;

    /**
     * The service config path.
     *
     * @var string
     */
    protected string $config;

    public function __construct(string|int $id)
    {
        $this->id = $id;

        if (is_null($config = Config::get("orchestration.services.{$id}"))) {
            throw new RuntimeException("Service with id {$id} does not exist.");
        }

        $this->config = is_array($config) ? "orchestration.services.{$id}" : $config;
    }

    /**
     * Gets an attribute from the fluent instance using "dot" notation.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Config::get("{$this->config}.{$key}", fn () => Config::get('orchestration.'.$key, $default));
    }

    /**
     * Sets an attribute to the fluent instance using "dot" notation.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        Config::set("{$this->config}.{$key}", $value);
    }

    /**
     * Gets the service id.
     *
     * @return string|int
     */
    public function getId(): string|int
    {
        return $this->id;
    }

    /**
     * Gets the service name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->get('name', $this->id);
    }

    /**
     * Gets all of the orchestration service ids.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all(): Collection
    {
        return Collection::make(Config::get('orchestration.services', []))->keys()->map(fn ($id) => new static($id));
    }

    /**
     * Gets the service's config by it's id.
     *
     * @param string|int $id
     * @return static|null
     */
    public static function find(string|int $id): ?static
    {
        try {
            return new static($id);
        } catch (RuntimeException $e) {
            return null;
        }
    }
}
