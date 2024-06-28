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
     * @var string
     */
    protected string $id;

    /**
     * The service config path.
     *
     * @var string
     */
    protected string $config;

    public function __construct($id)
    {
        $this->id = $id;

        if (is_null($config = Config::get("orchestration.services.{$id}"))) {
            throw new RuntimeException("Service with id {$id} does not exist.");
        }

        $this->config = is_array($config) ? "orchestration.services.{$id}" : $config;
    }

    /**
     * Get an attribute from the fluent instance using "dot" notation.
     *
     * @param  string  $key
     * @param  string|int|mixed|null $default
     * @return string|int|mixed|null
     */
    public function get($key, $default = null)
    {
        return Config::get("{$this->config}.{$key}", fn () => Config::get('orchestration.'.$key, $default));
    }

    /**
     * Set an attribute to the fluent instance using "dot" notation.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        Config::set("{$this->config}.{$key}", $value);
    }

    /**
     * Get the service id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the service name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->get('name', $this->id);
    }

    /**
     * Get all of the orchestration service ids.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        return Collection::make(Config::get('orchestration.services', []))->keys()->map(fn ($id) => new static($id));
    }

    /**
     * Get the service's config by it's id.
     *
     * @param string|int $id
     * @return static|null
     */
    public static function find($id)
    {
        try {
            return new static($id);
        } catch (RuntimeException $e) {
            return null;
        }
    }
}
