<?php

namespace Payavel\Serviceable\Tests\Services\Mock;

use Payavel\Serviceable\ServiceRequest;
use Payavel\Serviceable\Tests\Services\Mock\Contracts\MockRequestor;

class TestMockRequest extends ServiceRequest implements MockRequestor
{
    public function getIdentity()
    {
        return new TestMockResponse([]);
    }
}
