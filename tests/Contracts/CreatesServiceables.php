<?php

namespace Payavel\Orchestration\Tests\Contracts;

use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\ServiceConfig;

interface CreatesServiceables
{
    /**
     * Creates a service config instance.
     *
     * @param array $data
     * @return \Payavel\Orchestration\ServiceConfig
     */
    public function createServiceConfig($data = []);

    /**
     * Creates a providable instance.
     *
     * @param \Payavel\Orchestration\ServiceConfig $serviceConfig
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function createProvider(ServiceConfig $serviceConfig, $data = []);

    /**
     * Creates an accountable instance.
     *
     * @param \Payavel\Orchestration\ServiceConfig $serviceConfig
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function createAccount(ServiceConfig $serviceConfig, $data = []);

    /**
     * Links a accountable instance to a providable one.
     *
     * @param Accountable $account
     * @param Providable $provider
     * @param array $data
     * @return void
     */
    public function linkAccountToProvider(Accountable $account, Providable $provider, $data = []);

    /**
     * Sets the default configuration for a service.
     *
     * @param \Payavel\Orchestration\ServiceConfig $serviceConfig
     * @param Accountable|null $account
     * @param Providable|null $provider
     * @return void
     */
    public function setDefaultsForService(ServiceConfig $serviceConfig, Accountable $account = null, Providable $provider = null);
}
