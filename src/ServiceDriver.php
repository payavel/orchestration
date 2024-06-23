<?php

namespace Payavel\Orchestration;

use Illuminate\Support\Collection;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\ServiceConfig;

abstract class ServiceDriver
{
    /**
     * The service's config.
     *
     * @var \Payavel\Orchestration\Fluent\ServiceConfig
     */
    protected ServiceConfig $serviceConfig;

    /**
     * Set's the service's config.
     *
     * @param \Payavel\Orchestration\Fluent\ServiceConfig $serviceConfig
     * @return void
     */
    public function __construct(ServiceConfig $serviceConfig)
    {
        $this->serviceConfig = $serviceConfig;
    }

    /**
     * Refresh the driver's properties if necessary.
     *
     * @return void
     */
    public function refresh()
    {
        //
    }

    /**
     * Resolve the providable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string|int $provider
     * @return \Payavel\Orchestration\Contracts\Providable|null
     */
    abstract public function resolveProvider($provider);

    /**
     * Get the default providable identifier.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|null $account
     * @return string|int
     */
    abstract public function getDefaultProvider(Accountable $account = null);

    /**
     * Resolve the accountable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|string|int $account
     * @return \Payavel\Orchestration\Contracts\Accountable|null
     */
    abstract public function resolveAccount($account);

    /**
     * Get the default accountable identifier.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|null $provider
     * @return string|int
     */
    abstract public function getDefaultAccount(Providable $provider = null);

    /**
     * Resolve the gateway.
     *
     * @param \Payavel\Orchestration\Contracts\Providable $provider
     * @param \Payavel\Orchestration\Contracts\Accountable $account
     * @return \Payavel\Orchestration\ServiceRequest
     */
    abstract public function resolveGateway(Providable $provider, Accountable $account);

    /**
     * Generate the service skeleton based on the current driver.
     *
     * @param \Payavel\Orchestration\Fluent\ServiceConfig $serviceConfig
     * @param \Illuminate\Support\Collection $providers
     * @param \Illuminate\Support\Collection $accounts
     * @param array $defaults
     * @return void
     */
    public static function generateService(ServiceConfig $serviceConfig, Collection $providers, Collection $accounts, array $defaults)
    {
        //
    }
}
