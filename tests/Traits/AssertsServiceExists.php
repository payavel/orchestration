<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;

trait AssertsServiceExists
{
    protected function configPath(Serviceable $serviceable)
    {
        $service = Str::slug($serviceable->getId());

        return new Fluent([
            'orchestration' => "orchestration.php",
            'service' => "{$service}.php",
        ]);
    }

    protected function contractPath(Serviceable $serviceable)
    {
        $service = Str::studly($serviceable->getId());

        $ds = DIRECTORY_SEPARATOR;
        return new Fluent([
            'requester' => "Services{$ds}{$service}{$ds}Contracts{$ds}{$service}Requester.php",
            'responder' => "Services{$ds}{$service}{$ds}Contracts{$ds}{$service}Responder.php",
        ]);
    }

    protected function gatewayPath(Serviceable $serviceable)
    {
        if ($serviceable instanceof Providable) {
            $service = Str::studly($serviceable->getService()->getId());
            $provider = Str::studly($serviceable->getId());
        } else {
            $service = Str::studly($serviceable->getId());
            $provider = 'Fake';
        }

        $ds = DIRECTORY_SEPARATOR;
        return new Fluent([
            'request' => "Services{$ds}{$service}{$ds}{$provider}{$service}Request.php",
            'response' => "Services{$ds}{$service}{$ds}{$provider}{$service}Response.php",
        ]);
    }

    protected function assertConfigExists(Serviceable $serviceable)
    {
        $config = $this->configPath($serviceable);

        $this->assertFileExists(config_path($config->orchestration));
        $this->assertFileExists(config_path($config->service));
    }

    protected function assertContractExists(Serviceable $serviceable)
    {
        $contract = $this->contractPath($serviceable);

        $this->assertFileExists(app_path($contract->requester));
        $this->assertFileExists(app_path($contract->responder));
    }

    protected function assertGatewayExists(Serviceable $serviceable)
    {
        $gateway = $this->gatewayPath($serviceable);

        $this->assertFileExists(app_path($gateway->request));
        $this->assertFileExists(app_path($gateway->response));
    }
}
