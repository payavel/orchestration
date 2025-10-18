<?php

namespace Payavel\Orchestration\Tests\Services\Mock\Contracts;

interface MockRequester
{
    /**
     * Dummy method to assert against.
     *
     * @param bool $withAdditionalData
     *
     * @return \Payavel\Orchestration\ServiceResponse|mixed
     */
    public function getIdentity(bool $withAdditionalData = false): mixed;
}
