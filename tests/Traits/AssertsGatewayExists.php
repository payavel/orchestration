<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;

trait AssertsGatewayExists
{
    protected function assertGatewayExists(Serviceable $serviceable)
    {
        if ($serviceable instanceof Providable) {
            $service = Str::studly($serviceable->getService()->getId());
            $provider = Str::studly($serviceable->getId());
        } else {
            $service = Str::studly($serviceable->getId());
            $provider = 'Fake';
        }

        $servicePath = app_path("Services/{$service}");

        $this->assertTrue(file_exists("{$servicePath}/{$provider}{$service}Request.php"));
        $this->assertTrue(file_exists("{$servicePath}/{$provider}{$service}Response.php"));
    }
}
