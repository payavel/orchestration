<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;

trait AssertsGatewayExists
{
    protected function gatewayPath(Serviceable $serviceable)
    {
        if ($serviceable instanceof Providable) {
            $service = Str::studly($serviceable->getService()->getId());
            $provider = Str::studly($serviceable->getId());
        } else {
            $service = Str::studly($serviceable->getId());
            $provider = 'Fake';
        }

        return new Fluent([
            'request' => "Services/{$service}/{$provider}{$service}Request.php",
            'response' => "Services/{$service}/{$provider}{$service}Response.php",
        ]);
    }

    protected function assertGatewayExists(Serviceable $serviceable)
    {
        $gateway = $this->gatewayPath($serviceable);

        $this->assertTrue(file_exists(app_path($gateway->request)));
        $this->assertTrue(file_exists(app_path($gateway->response)));
    }
}
