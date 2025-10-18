<?php

namespace Payavel\Orchestration\Tests\Services\Mock\Contracts;

interface MockResponder
{
    /**
     * Dummy method to assert against.
     *
     * @return mixed
     */
    public function getIdentityResponse(): mixed;
}
