<?php

namespace Payavel\Orchestration\Contracts;

interface Orchestrable
{
    /**
     * Gets the orchestrable service.
     *
     * @return \Payavel\Orchestration\Service
     */
    public function getService();

    /**
     * Gets the orchestrable service's provider.
     *
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function getProvider();

    /**
     * Gets the orchestrable service's account.
     *
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function getAccount();
}
