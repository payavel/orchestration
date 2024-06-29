<?php

namespace Payavel\Orchestration\Fluent;

use Illuminate\Support\Fluent;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\ServiceConfig;

class Account extends Fluent implements Accountable
{
    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\ServiceConfig
     */
    protected ServiceConfig $serviceConfig;

    public function __construct(ServiceConfig $serviceConfig, $id)
    {
        $this->serviceConfig = $serviceConfig;

        parent::__construct([
            'id' => $id,
            ...$serviceConfig->get("accounts.{$id}", []),
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

        $this->serviceConfig->set("accounts.{$this->attributes['id']}.{$key}", $value);
    }

    /**
     * Get the accountable id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->attributes['id'];
    }

    /**
     * Get the accountable name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->attributes['name'] ?? $this->attributes['id'];
    }
}
