<?php

namespace Payavel\Serviceable;

use Exception;
use Illuminate\Support\Facades\Config;
use Payavel\Serviceable\Contracts\Serviceable;
use Payavel\Serviceable\Traits\ServiceableConfig;
use Payavel\Serviceable\Traits\SimulateAttributes;

class Service
{
    use ServiceableConfig,
        SimulateAttributes;

    /**
     * The service.
     *
     * @var \Payavel\Serviceable\Contracts\Serviceable
     */
    private $service;

    /**
     * The service driver that will handle provider & merchant configurations.
     *
     * @var \Payavel\Serviceable\ServiceDriver
     */
    private $driver;

    /**
     * The provider requests will be forwarded to.
     *
     * @var \Payavel\Serviceable\Contracts\Providable
     */
    private $provider;

    /**
     * The merchant that will be passed to the provider's gateway.
     *
     * @var \Payavel\Serviceable\Contracts\Merchantable
     */
    private $merchant;

    /**
     * The gateway class where requests will be executed.
     *
     * @var \Payavel\Serviceable\ServiceRequest
     */
    private $gateway;

    /**
     * Determines the service and sets up the driver for it.
     *
     * @param \Payavel\Serviceable\Contracts\Serviceable $service
     * @return void
     *
     * @throws Exception
     */
    public function __construct(Serviceable $service)
    {
        $this->service = $service;

        if (! class_exists($driver = $this->config($this->service->getId(), 'drivers.' . $this->config($this->service->getId(), 'defaults.driver')))) {
            throw new Exception('Invalid serviceable driver provided.');
        }

        $this->driver = new $driver($this->service);
    }

    /**
     * Fluent provider setter.
     *
     * @param \Payavel\Serviceable\Contracts\Providable|string|int $provider
     * @return \Payavel\Serviceable\Service
     */
    public function provider($provider)
    {
        $this->setProvider($provider);

        return $this;
    }

    /**
     * Get the current provider.
     *
     * @return \Payavel\Serviceable\Contracts\Providable
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
     * @param \Payavel\Serviceable\Contracts\Providable|string|int $provider
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
     * @return string|int|\Payavel\Serviceable\Contracts\Providable
     */
    public function getDefaultProvider()
    {
        return $this->driver->getDefaultProvider($this->merchant);
    }

    /**
     * Fluent merchant setter.
     *
     * @param \Payavel\Serviceable\Contracts\Merchantable|string|int $merchant
     * @return \Payavel\Serviceable\Service
     */
    public function merchant($merchant)
    {
        $this->setMerchant($merchant);

        return $this;
    }

    /**
     * Get the current merchant.
     *
     * @return \Payavel\Serviceable\Contracts\Merchantable
     */
    public function getMerchant()
    {
        if (! isset($this->merchant)) {
            $this->setMerchant($this->getDefaultMerchant());
        }

        return $this->merchant;
    }

    /**
     * Set the specified merchant.
     *
     * @param \Payavel\Serviceable\Contracts\Merchantable|string|int $merchant
     * @return void
     *
     * @throws Exception
     */
    public function setMerchant($merchant)
    {
        if (is_null($merchant = $this->driver->resolveMerchant($merchant))) {
            throw new Exception('Invalid merchant.');
        }

        $this->merchant = $merchant;

        $this->gateway = null;
    }

    /**
     * Get the default merchant.
     *
     * @return string|int|\Payavel\Serviceable\Contracts\Merchantable
     */
    public function getDefaultMerchant()
    {
        return $this->driver->getDefaultMerchant($this->provider);
    }

    /**
     * Get the serviceable gateway.
     *
     * @return \Payavel\Serviceable\ServiceRequest
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
     *
     * @throws Exception
     */
    protected function setGateway()
    {
        $provider = $this->getProvider();
        $merchant = $this->getMerchant();

        if (! $this->driver->check($provider, $merchant)) {
            throw new Exception("The {$merchant->getName()} merchant is not supported by the {$provider->getName()} provider.");
        }

        $gateway = $this->config($this->service->getId(), 'test_mode')
            ? $this->config($this->service->getId(), 'mock.request_class')
            : $this->driver->resolveGatewayClass($provider);

        if (! class_exists($gateway)) {
            throw new Exception(
                is_null($gateway)
                    ? "You must set a request_class for the {$provider->getName()} {$this->service->getName()} provider."
                    : "The {$gateway}::class does not exist."
            );
        }

        $this->gateway = new $gateway($provider, $merchant);
    }

    /**
     * Reset the service to its defaults.
     *
     * @return void
     */
    public function reset()
    {
        $this->provider = null;
        $this->merchant = null;
        $this->gateway = null;
    }

    /**
     * @param string $method
     * @param array $params
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $params)
    {
        if (! method_exists($this->gateway, $method)) {
            throw new \BadMethodCallException(__CLASS__ . "::{$method}() not found.");
        }

        return tap($this->gateway->{$method}(...$params))->configure($method, $this->provider, $this->merchant);
    }

    /**
     * Get the default serviceable driver.
     *
     * @return string|int
     */
    private static function driver()
    {
        return Config::get('serviceable.drivers.' . Config::get('serviceable.defaults.driver'));
    }

    /**
     * Retrieve all service ids.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        return static::driver()::services();
    }

    /**
     * @param string|int $id
     * @return \Payavel\Serviceable\Contracts\Serviceable|null
     */
    public static function find($id)
    {
        return static::all()->first(fn ($service) => $service->getId() == $id);
    }
}
