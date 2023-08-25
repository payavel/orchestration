<?php

namespace Payavel\Serviceable\Tests\Services\Mock;

use Payavel\Serviceable\ServiceResponse;
use Payavel\Serviceable\Tests\Services\Mock\Contracts\MockResponder;

class FakeMockResponse extends ServiceResponse implements MockResponder
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

    public function getProviderResponse()
    {
        return $this->provider;
    }

    public function getMerchantResponse()
    {
        return $this->merchant;
    }

    public function getIdentityResponse()
    {
        return 'Fake';
    }
}
