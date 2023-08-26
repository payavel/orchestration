<?php

namespace Payavel\Serviceable\Tests\Services\Mock;

use Payavel\Serviceable\ServiceResponse;
use Payavel\Serviceable\Tests\Services\Mock\Contracts\MockResponder;

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
    public function getStatus()
    {
        return 'Success';
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return 'All good for now!';
    }

    public function getIdentityResponse()
    {
        return 'Real';
    }
}
