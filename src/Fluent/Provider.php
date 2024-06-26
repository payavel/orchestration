<?php

namespace Payavel\Orchestration\Fluent;

use Illuminate\Support\Fluent;
use Payavel\Orchestration\Contracts\Providable;

class Provider extends Fluent implements Providable
{
    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\Fluent\ServiceConfig
     */
    public ServiceConfig $serviceConfig;

    public function __construct(ServiceConfig $serviceConfig, $id)
    {
        $this->serviceConfig = $serviceConfig;

        parent::__construct([
            'id' => $id,
            ...$serviceConfig->get("providers.{$id}"),
        ]);
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
        return data_get($this->attributes, $key, value($default));
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
        data_set($this->attributes, $key, $value);

        $this->serviceConfig->set("providers.{$this->attributes['id']}.{$key}", $value);
    }

    /**
     * Get the providable id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->attributes['id'];
    }

    /**
     * Get the providable name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->attributes['name'] ?? $this->attributes['id'];
    }
}
