<?php

namespace Payavel\Orchestration;

use Illuminate\Support\Collection;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;

abstract class ServiceDriver
{
    /**
     * The compatible service.
     *
     * @var \Payavel\Orchestration\Contracts\Serviceable
     */
    protected Serviceable $service;

    /**
     * Assigns the service to the driver.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @return void
     */
    public function __construct(Serviceable $service)
    {
        $this->service = $service;
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
    abstract public function resolveGateway($provider, $account);

    /**
     * Generate the service skeleton based on the current driver.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @param \Illuminate\Support\Collection $providers
     * @param \Illuminate\Support\Collection $accounts
     * @param array $defaults
     * @return void
     */
    public static function generateService(Serviceable $service, Collection $providers, Collection $accounts, array $defaults)
    {
        //
    }
}
