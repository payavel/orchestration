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
     * @var string|int
     */
    protected ServiceConfig $serviceConfig;

    public function __construct(ServiceConfig $serviceConfig, string|int $id)
    {
        $this->serviceConfig = $serviceConfig;

        parent::__construct([
            'id' => $id,
            ...$serviceConfig->get("accounts.{$id}", []),
        ]);
    }

    /**
     * Gets an attribute from the fluent instance using "dot" notation.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
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
     * Gets the accountable id.
     *
     * @return string|int
     */
    public function getId(): string|int
    {
        return $this->attributes['id'];
    }

    /**
     * Gets the accountable name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->attributes['name'] ?? $this->attributes['id'];
    }
}
