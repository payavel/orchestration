<?php

namespace Payavel\Orchestration\Tests\Services\Mock;

use Payavel\Orchestration\ServiceResponse;
use Payavel\Orchestration\Tests\Services\Mock\Contracts\MockResponder;

class TestMockResponse extends ServiceResponse implements MockResponder
{

    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function getStatusMessage()
    {
        return 'Success';
    }

    /**
     * @inheritDoc
     */
    public function getStatusDescription()
    {
        return 'All good for now!';
    }

    public function getIdentityResponse()
    {
        return 'Real';
    }
}
