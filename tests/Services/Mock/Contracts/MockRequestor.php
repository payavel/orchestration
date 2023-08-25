<?php

namespace Payavel\Serviceable\Tests\Services\Mock\Contracts;

interface MockRequestor
{
    public function getProvider();

    public function getMerchant();

    public function getIdentity();
}
