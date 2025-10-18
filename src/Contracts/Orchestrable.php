<?php

namespace Payavel\Orchestration\Contracts;

use Payavel\Orchestration\Service;

interface Orchestrable
{
    /**
     * Gets the orchestrable service.
     *
     * @return \Payavel\Orchestration\Service
     */
    public function getService(): Service;

    /**
     * Gets the orchestrable service's provider.
     *
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function getProvider(): Providable;

    /**
     * Gets the orchestrable service's account.
     *
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function getAccount(): Accountable;
}
