<?php

namespace Payavel\Orchestration\Traits;

use Payavel\Orchestration\Service;

trait OrchestratesService
{
    /**
     * The orchestrated service.
     *
     * @param \Payavel\Orchestration\Service
     */
    private $orchestratedService;

    /**
     * Gets the orchestrable service. Also sets it if it hasn't been resolved yet.
     *
     * @return \Payavel\Orchestration\Service
     */
    public function getService()
    {
        if (! isset($this->orchestratedService)) {
            $this->orchestratedService = (new Service($this->service_id ?? $this->serviceId))
                ->provider($this->provider_id ?? $this->providerId)
                ->account($this->account_id ?? $this->accountId);
        }

        return $this->orchestratedService;
    }

    /**
     * Gets the orchestrable service's provider.
     *
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function getProvider()
    {
        return $this->getService()->getProvider();
    }

    /**
     * Gets the orchestrable service's account.
     *
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function getAccount()
    {
        return $this->getService()->getAccount();
    }

    /**
     * Retrieve the service as a property when applied to a Model.
     *
     * @return \Payavel\Orchestration\Service
     */
    protected function getServiceAttribute()
    {
        return $this->getService();
    }
}
