<?php

namespace Payavel\Orchestration;

use Exception;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\ServiceConfig;
use Payavel\Orchestration\Traits\SimulatesAttributes;

class Service
{
    use SimulatesAttributes;

    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\ServiceConfig
     */
    protected ServiceConfig $config;

    /**
     * The service driver that will handle provider/account gateway resolutions.
     *
     * @var \Payavel\Orchestration\ServiceDriver
     */
    protected ServiceDriver $driver;

    /**
     * The configured provider.
     *
     * @var \Payavel\Orchestration\Contracts\Providable
     */
    protected ?Providable $provider = null;

    /**
     * The configured account.
     *
     * @var \Payavel\Orchestration\Contracts\Accountable
     */
    protected ?Accountable $account = null;

    /**
     * The gateway class where requests will be executed.
     *
     * @var \Payavel\Orchestration\ServiceRequest|null
     */
    protected ?ServiceRequest $gateway = null;

    /**
     * Sets the service config and the driver for it.
     *
     * @param \Payavel\Orchestration\ServiceConfig|string|int $serviceConfig
     * @return void
     *
     * @throws Exception
     */
    public function __construct(ServiceConfig|string|int $serviceConfig)
    {
        if (! $serviceConfig instanceof ServiceConfig && is_null($serviceConfig = ServiceConfig::find($serviceConfig))) {
            throw new Exception("Service config with id '{$serviceConfig}' was not found.");
        }

        $this->config = $serviceConfig;

        if (! class_exists($driver = $this->config->get('drivers.'.$this->config->get('defaults.driver')))) {
            throw new Exception('Invalid driver provided.');
        }

        $this->driver = new $driver($this->config);
    }

    /**
     * Sets the provider fluently.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string|int $provider
     * @return \Payavel\Orchestration\Service
     */
    public function provider(Providable|string|int $provider): static
    {
        $this->setProvider($provider);

        return $this;
    }

    /**
     * Gets the current provider.
     *
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function getProvider(): Providable
    {
        if (! isset($this->provider)) {
            $this->setProvider($this->getDefaultProvider());
        }

        return $this->provider;
    }

    /**
     * Sets the provider.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string|int $provider
     * @return void
     *
     * @throws Exception
     */
    public function setProvider(Providable|string|int $provider): void
    {
        if (is_null($provider = $this->driver->resolveProvider($provider))) {
            throw new Exception('Invalid provider.');
        }

        $this->provider = $provider;

        $this->gateway = null;
    }

    /**
     * Gets the default service provider.
     *
     * @return string|int|\Payavel\Orchestration\Contracts\Providable
     */
    public function getDefaultProvider(): string|int|Providable
    {
        return $this->driver->getDefaultProvider($this->account);
    }

    /**
     * Sets the account fluently.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|string|int $account
     * @return \Payavel\Orchestration\Service
     */
    public function account(Accountable|string|int $account): static
    {
        $this->setAccount($account);

        return $this;
    }

    /**
     * Gets the current account.
     *
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function getAccount(): Accountable
    {
        if (! isset($this->account)) {
            $this->setAccount($this->getDefaultAccount());
        }

        return $this->account;
    }

    /**
     * Sets the specified account.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|string|int $account
     * @return void
     *
     * @throws Exception
     */
    public function setAccount(Accountable|string|int $account): void
    {
        if (is_null($account = $this->driver->resolveAccount($account))) {
            throw new Exception('Invalid account.');
        }

        $this->account = $account;

        $this->gateway = null;
    }

    /**
     * Gets the default account.
     *
     * @return string|int|\Payavel\Orchestration\Contracts\Accountable
     */
    public function getDefaultAccount(): string|int|Accountable
    {
        return $this->driver->getDefaultAccount($this->provider);
    }

    /**
     * Gets the service's gateway.
     *
     * @return \Payavel\Orchestration\ServiceRequest
     */
    protected function getGateway(): ServiceRequest
    {
        if (! isset($this->gateway)) {
            $this->setGateway();
        }

        return $this->gateway;
    }

    /**
     * Resolves a new instance of the service's gateway.
     *
     * @return void
     */
    protected function setGateway(): void
    {
        $provider = $this->getProvider();
        $account = $this->getAccount();

        $this->gateway = $this->driver->resolveGateway($provider, $account);
    }

    /**
     * Resets the service to its defaults.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->driver->refresh();

        $this->gateway = $this->account = $this->provider = null;
    }

    /**
     * @param string $method
     * @param array $params
     *
     * @return \Payavel\Orchestration\ServiceResponse|mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $params = []): mixed
    {
        return $this->getGateway()->request($method, $params);
    }
}
