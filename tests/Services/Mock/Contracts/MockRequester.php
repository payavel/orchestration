<?php

namespace Payavel\Orchestration\Tests\Services\Mock\Contracts;

use Payavel\Orchestration\ServiceResponse;

interface MockRequester
{
    /**
     * Dummy method to assert against.
     *
     * @param bool $withAdditionalData
     *
     * @return \Payavel\Orchestration\ServiceResponse
     */
    public function getIdentity(bool $withAdditionalData = false): ServiceResponse;
}
