<?php

namespace Payavel\Serviceable\Tests\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Serviceable\DataTransferObjects\Merchant as MerchantDto;
use Payavel\Serviceable\DataTransferObjects\Provider as ProviderDto;
use Payavel\Serviceable\DataTransferObjects\Service as ServiceDto;
use Payavel\Serviceable\Models\Merchant as MerchantModel;
use Payavel\Serviceable\Models\Provider as ProviderModel;
use Payavel\Serviceable\Models\Service as ServiceModel;

trait CreateServiceables
{
    protected function createService($data = [])
    {
        $createService = 'createService' . Str::studly(Config::get('serviceable.defaults.driver'));

        return $this->$createService($data);
    }

    protected function createServiceConfig($data)
    {
        $data['name'] = $data['name'] ?? $this->faker->unique()->word();
        $data['id'] = $data['id'] ?? Str::lower($data['name']);

        Config::set('serviceable.services.' . $data['id'], [
            'name' => $data['name'],
            'config' => Str::slug($data['id']),
        ]);

        return new ServiceDto($data);;
    }

    protected function createServiceDatabase($data)
    {
        return ServiceModel::factory()->create($data);
    }

    protected function createProvider($service = null, $data = [])
{
    if (is_null($service)) {
        $service = $this->createService();
    }

    $createProvider = 'createProvider' . Str::studly(Config::get('serviceable.defaults.driver'));

    return $this->$createProvider($service, $data);
}

    protected function createProviderConfig($service, $data)
    {
        $data['name'] = $data['name'] ?? Str::remove(['\'', ','], $this->faker->unique()->company());
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower($data['name']));
        $data['request_class'] = $data['request_class'] ?? 'App\\Services\\' . Str::studly($service->getId()) . '\\' . Str::studly($data['id']) . Str::studly($service->getId()) . 'Request';
        $data['response_class'] = $data['response_class'] ?? 'App\\Services\\' . Str::studly($service->getId()) . '\\' . Str::studly($data['id']) . Str::studly($service->getId()) . 'Response';

        Config::set(Str::slug($service->getId()) . '.providers.' . $data['id'], [
            'name' => $data['name'],
            'request_class' => $data['request_class'],
            'response_class' => $data['response_class'],
        ]);

        return new ProviderDto($service, $data);
    }

    protected function createProviderDatabase($service, $data)
    {
        $data['service_id'] = $service->getId();

        return ProviderModel::factory()->create($data);
    }

    protected function createMerchant($service = null, $data = [])
    {
        if (is_null($service)) {
            $service = $this->createService();
        }

        $createMerchant = 'createMerchant' . Str::studly(Config::get('serviceable.defaults.driver'));

        return $this->$createMerchant($service, $data);
    }

    protected function createMerchantConfig($service, $data)
    {
        $data['name'] = $data['name'] ?? Str::remove(['\'', ','], $this->faker->unique()->company());
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower($data['name']));

        Config::set(Str::slug($service->getId()) . '.merchants.' . $data['id'], [
            'name' => $data['name'],
        ]);

        return new MerchantDto($service, $data);
    }

    protected function createMerchantDatabase($service, $data)
    {
        $data['service_id'] = $service->getId();

        return MerchantModel::factory()->create($data);
    }
}
