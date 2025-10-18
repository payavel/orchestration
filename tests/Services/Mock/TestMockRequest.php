<?php

namespace Payavel\Orchestration\Tests\Services\Mock;

use Payavel\Orchestration\ServiceRequest;
use Payavel\Orchestration\Tests\Services\Mock\Contracts\MockRequester;

class TestMockRequest extends ServiceRequest implements MockRequester
{
    public function getIdentity(bool $withAdditionalData = false): mixed
    {
        $response = [];

        if ($withAdditionalData) {
            $response = $this->response($response)->with('additional data');
        }

        return $response;
    }
}
