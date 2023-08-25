<?php

namespace Payavel\Serviceable\Tests\Services\Mock;

use Payavel\Serviceable\ServiceRequest;
use Payavel\Serviceable\Tests\Services\Mock\Contracts\MockRequestor;

class FakeMockRequest extends ServiceRequest implements MockRequestor
{

    public function getProvider()
    {
        return new FakeMockResponse([]);
    }

    public function getMerchant()
    {
        return new FakeMockResponse([]);
    }

    public function getIdentity()
    {
        return new FakeMockResponse([]);
    }
}
