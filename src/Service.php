<?php

namespace Payavel\Serviceable;

use Exception;
use Payavel\Serviceable\Traits\SimulateAttributes;

class Service
{
    use SimulateAttributes;

    /**
     * The service.
     *
     * @var \Payavel\Serviceable\Contracts\Serviceable
     */
    private $service;

    /**
     * The service driver that will handle provider & merchant configurations.
     *
     * @var \Payavel\Serviceable\PaymentServiceDriver
     */
    private $driver;

    /**
     * The payment provider requests will be forwarded to.
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
     * @var \Payavel\Serviceable\PaymentRequest
     */
    private $gateway;

    /**
     * Prepares the driver based on preference determined in config file.
     *
     * @return void
     *
     * @throws Exception
     */
    public function __construct()
    {
        if (! class_exists($driver = config('serviceable.drivers.' . config('serviceable.defaults.driver', 'config')))) {
            throw new Exception('Invalid serviceable driver provided.');
        }

        $this->driver = new $driver;
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

        $gateway = config($provider->getId() . '.test_mode')
            ? config($provider->getId() . '.mocking.request_class')
            : $this->driver->resolveGatewayClass($provider);

        if (! class_exists($gateway)) {
            throw new Exception('The ' . $gateway . '::class does not exist.');
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
            throw new BadMethodCallException(__CLASS__ . "::{$method}() not found.");
        }

        return tap($this->gateway->{$method}(...$params))->configure($method, $this->provider, $this->merchant);
    }

    /**
     * Retrieve all service ids.
     *
     * @return array
     */
    public static function ids()
    {
        $driver = config('serviceable.drivers.' . config('serviceable.defaults.driver', 'config'));

        return $driver::services()->map(fn ($service) => $service->getId())->all();
    }
}
