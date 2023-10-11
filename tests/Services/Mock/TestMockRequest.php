<?php

namespace Payavel\Orchestration\Tests\Services\Mock;

use Payavel\Orchestration\ServiceRequest;
use Payavel\Orchestration\Tests\Services\Mock\Contracts\MockRequestor;

class TestMockRequest extends ServiceRequest implements MockRequestor
{
    public function getIdentity()
    {
        return new TestMockResponse([]);
    }
}
