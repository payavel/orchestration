<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\ServiceConfig;

trait AssertsServiceExists
{
    protected function configPath(ServiceConfig $serviceConfig)
    {
        $service = Str::slug($serviceConfig->id);

        return new Fluent([
            'orchestration' => "orchestration.php",
            'service' => "{$service}.php",
        ]);
    }

    protected function contractPath(ServiceConfig $serviceConfig)
    {
        $service = Str::studly($serviceConfig->id);

        $ds = DIRECTORY_SEPARATOR;
        return new Fluent([
            'requester' => "Services{$ds}{$service}{$ds}Contracts{$ds}{$service}Requester.php",
            'responder' => "Services{$ds}{$service}{$ds}Contracts{$ds}{$service}Responder.php",
        ]);
    }

    protected function gatewayPath(ServiceConfig $serviceConfig, Providable $provider = null)
    {
        $service = Str::studly($serviceConfig->id);
        $provider = is_null($provider) ? 'Fake' : Str::studly($provider->getId());

        $ds = DIRECTORY_SEPARATOR;
        return new Fluent([
            'request' => "Services{$ds}{$service}{$ds}{$provider}{$service}Request.php",
            'response' => "Services{$ds}{$service}{$ds}{$provider}{$service}Response.php",
        ]);
    }

    protected function assertConfigExists(ServiceConfig $serviceConfig)
    {
        $configPath = $this->configPath($serviceConfig);

        $this->assertFileExists(config_path($configPath->orchestration));
        $this->assertFileExists(config_path($configPath->service));
    }

    protected function assertContractExists(ServiceConfig $serviceConfig)
    {
        $contractPath = $this->contractPath($serviceConfig);

        $this->assertFileExists(app_path($contractPath->requester));
        $this->assertFileExists(app_path($contractPath->responder));
    }

    protected function assertGatewayExists(ServiceConfig $serviceConfig, Providable $provider = null)
    {
        $gatewayPath = $this->gatewayPath($serviceConfig, $provider);

        $this->assertFileExists(app_path($gatewayPath->request));
        $this->assertFileExists(app_path($gatewayPath->response));
    }
}
