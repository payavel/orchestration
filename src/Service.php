<?php

namespace Payavel\Orchestration;

use Exception;
use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Fluent\Config as FluentConfig;
use Payavel\Orchestration\Traits\SimulatesAttributes;

class Service
{
    use SimulatesAttributes;

    /**
     * The service configurations.
     *
     * @var \Payavel\Orchestration\Fluent\Config
     */
    private $config;

    /**
     * The service driver that will handle provider & account configurations.
     *
     * @var \Payavel\Orchestration\ServiceDriver
     */
    private $driver;

    /**
     * The provider requests will be forwarded to.
     *
     * @var \Payavel\Orchestration\Contracts\Providable
     */
    private $provider;

    /**
     * The account that will be passed to the provider's gateway.
     *
     * @var \Payavel\Orchestration\Contracts\Accountable
     */
    private $account;

    /**
     * The gateway class where requests will be executed.
     *
     * @var \Payavel\Orchestration\ServiceRequest
     */
    private $gateway;

    /**
     * Sets the service config and the driver for it.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable|string $config
     * @deprecated Use \Payavel\Orchestration\Fluent\Config type as param instead of \Payavel\Orchestration\Contracts\Serviceable.
     * @return void
     *
     * @throws Exception
     */
    public function __construct($config)
    {
        if (! $config instanceof Serviceable && is_null($config = static::find($config))) {
            throw new Exception("Service config with id '{$config}' was not found.");
        }

        $this->config = $config;

        if (! class_exists($driver = $this->config->get('drivers.'.$this->config->get('defaults.driver')))) {
            throw new Exception('Invalid driver provided.');
        }

        $this->driver = new $driver($this->config);
    }

    /**
     * Fluent provider setter.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string|int $provider
     * @return \Payavel\Orchestration\Service
     */
    public function provider($provider)
    {
        $this->setProvider($provider);

        return $this;
    }

    /**
     * Get the current provider.
     *
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function getProvider()
    {
        if (! isset($this->provider)) {
            $this->setProvider($this->getDefaultProvider());
        }

        return $this->provider;
    }

    /**
     * Set the provider.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string|int $provider
     * @return void
     *
     * @throws Exception
     */
    public function setProvider($provider)
    {
        if (is_null($provider = $this->driver->resolveProvider($provider))) {
            throw new Exception('Invalid provider.');
        }

        $this->provider = $provider;

        $this->gateway = null;
    }

    /**
     * Get the default service provider.
     *
     * @return string|int|\Payavel\Orchestration\Contracts\Providable
     */
    public function getDefaultProvider()
    {
        return $this->driver->getDefaultProvider($this->account);
    }

    /**
     * Fluent account setter.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|string|int $account
     * @return \Payavel\Orchestration\Service
     */
    public function account($account)
    {
        $this->setAccount($account);

        return $this;
    }

    /**
     * Get the current account.
     *
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function getAccount()
    {
        if (! isset($this->account)) {
            $this->setAccount($this->getDefaultAccount());
        }

        return $this->account;
    }

    /**
     * Set the specified account.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|string|int $account
     * @return void
     *
     * @throws Exception
     */
    public function setAccount($account)
    {
        if (is_null($account = $this->driver->resolveAccount($account))) {
            throw new Exception('Invalid account.');
        }

        $this->account = $account;

        $this->gateway = null;
    }

    /**
     * Get the default account.
     *
     * @return string|int|\Payavel\Orchestration\Contracts\Accountable
     */
    public function getDefaultAccount()
    {
        return $this->driver->getDefaultAccount($this->provider);
    }

    /**
     * Get the serviceable gateway.
     *
     * @return \Payavel\Orchestration\ServiceRequest
     */
    protected function getGateway()
    {
        if (! isset($this->gateway)) {
            $this->setGateway();
        }

        return $this->gateway;
    }

    /**
     * Instantiate a new instance of the serviceable gateway.
     *
     * @return void
     */
    protected function setGateway()
    {
        $provider = $this->getProvider();
        $account = $this->getAccount();

        $this->gateway = $this->driver->resolveGateway($provider, $account);
    }

    /**
     * Reset the service to its defaults.
     *
     * @return void
     */
    public function reset()
    {
        $this->driver->refresh();

        $this->gateway = $this->account = $this->provider = null;
    }

    /**
     * @param string $method
     * @param array $params
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $params)
    {
        return $this->getGateway()->request($method, $params);
    }

    /**
     * Retrieve all service configs.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        return collect(Config::get('orchestration.services', []))->map(
            fn ($value, $key) => new FluentConfig(array_merge(['id' => $key], is_array($value) ? $value : Config::get($value)))
        )->values();
    }

    /**
     * Find a service config using it's id.
     *
     * @deprecated Use \Payavel\Orchestration\Fluent\Config type as return type instead of \Payavel\Orchestration\Contracts\Serviceable.
     *
     * @param string|int $id
     * @return \Payavel\Orchestration\Contracts\Serviceable|null
     */
    public static function find($id)
    {
        return static::all()->first(fn ($config) => $config->getId() == $id);
    }
}
