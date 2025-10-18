<?php

namespace Payavel\Orchestration\Tests\Services\Mock;

use Payavel\Orchestration\ServiceResponse;
use Payavel\Orchestration\Tests\Services\Mock\Contracts\MockResponder;

class FakeMockResponse extends ServiceResponse implements MockResponder
{
    public function getStatusCode(): int
    {
        return 200;
    }

    public function getStatusMessage(): string
    {
        return 'Success';
    }

    public function getStatusDescription(): string
    {
        return 'All good for now!';
    }

    public function getIdentityResponse(): string
    {
        return 'Fake'.(is_null($this->additionalData) ? '' : ' with '.$this->additionalData);
    }
}
