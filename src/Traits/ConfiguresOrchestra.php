<?php

namespace Payavel\Orchestration\Traits;

use Payavel\Orchestration\Service;

trait ConfiguresOrchestra
{
    private $service;

    protected function getGatewayAttribute()
    {
        return $this->getService();
    }

    protected function getService()
    {
        if (! isset($this->service)) {
            $this->setService();
        }

        return $this->service;
    }

    protected function setService()
    {
        $this->service = (new Service($this->service_id ?? $this->serviceId))
            ->provider($this->providerId ?? $this->provider_id)
            ->account($this->account_id ?? $this->accountId);
    }

    public function getProvider()
    {
        return $this->getService()->getProvider();
    }

    public function getAccount()
    {
        return $this->getService()->getAccount();
    }
}
