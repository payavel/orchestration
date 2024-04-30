<?php

namespace Payavel\Orchestration\Tests\Services\Mock;

use Payavel\Orchestration\ServiceRequest;
use Payavel\Orchestration\Tests\Services\Mock\Contracts\MockRequester;

class FakeMockRequest extends ServiceRequest implements MockRequester
{
    public function getIdentity($withAdditionalData = false)
    {
        $response = [];

        if ($withAdditionalData) {
            $response = $this->response($response)->with('additional data');
        }

        return $response;
    }
}
