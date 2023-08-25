<?php

namespace Payavel\Serviceable\Tests\Services\Mock\Contracts;

interface MockResponder
{
    public function getProviderResponse();

    public function getMerchantResponse();

    public function getIdentityResponse();
}
