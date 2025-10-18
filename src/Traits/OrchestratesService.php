<?php

namespace Payavel\Orchestration\Traits;

use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Service;

trait OrchestratesService
{
    /**
     * The orchestrated service.
     *
     * @param \Payavel\Orchestration\Service
     */
    private Service $orchestratedService;

    /**
     * Gets the orchestrable service, or sets it if it hasn't been resolved yet.
     *
     * @return \Payavel\Orchestration\Service
     */
    public function getService(): Service
    {
        if (! isset($this->orchestratedService)) {
            $this->orchestratedService = (new Service($this->service_id ?? $this->serviceId))
                ->provider($this->providerId ?? $this->provider_id)
                ->account($this->account_id ?? $this->accountId);
        }

        return $this->orchestratedService;
    }

    /**
     * Gets the orchestrable service's provider.
     *
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function getProvider(): Providable
    {
        return $this->getService()->getProvider();
    }

    /**
     * Gets the orchestrable service's account.
     *
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function getAccount(): Accountable
    {
        return $this->getService()->getAccount();
    }

    /**
     * Retrieves the service as a property when applied to a Model.
     *
     * @return \Payavel\Orchestration\Service
     */
    protected function getServiceAttribute(): Service
    {
        return $this->getService();
    }
}
