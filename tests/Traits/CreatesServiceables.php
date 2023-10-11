<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\DataTransferObjects\Merchant as MerchantDto;
use Payavel\Orchestration\DataTransferObjects\Provider as ProviderDto;
use Payavel\Orchestration\DataTransferObjects\Service as ServiceDto;
use Payavel\Orchestration\Models\Merchant as MerchantModel;
use Payavel\Orchestration\Models\Provider as ProviderModel;
use Payavel\Orchestration\Models\Service as ServiceModel;

trait CreatesServiceables
{
    protected function createService($data = [])
    {
        $createService = 'createService' . Str::studly(Config::get('orchestration.defaults.driver'));

        return $this->$createService($data);
    }

    protected function createServiceConfig($data)
    {
        $data['id'] = $data['id'] ?? Str::lower($this->faker->unique()->word());

        Config::set('orchestration.services.' . $data['id'], [
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

    $createProvider = 'createProvider' . Str::studly(Config::get('orchestration.defaults.driver'));

    return $this->$createProvider($service, $data);
}

    protected function createProviderConfig($service, $data)
    {
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower(Str::remove(['\'', ','], $this->faker->unique()->company())));
        $data['request_class'] = $data['request_class'] ?? 'App\\Services\\' . Str::studly($service->getId()) . '\\' . Str::studly($data['id']) . Str::studly($service->getId()) . 'Request';
        $data['response_class'] = $data['response_class'] ?? 'App\\Services\\' . Str::studly($service->getId()) . '\\' . Str::studly($data['id']) . Str::studly($service->getId()) . 'Response';

        Config::set(Str::slug($service->getId()) . '.providers.' . $data['id'], [
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

        $createMerchant = 'createMerchant' . Str::studly(Config::get('orchestration.defaults.driver'));

        return $this->$createMerchant($service, $data);
    }

    protected function createMerchantConfig($service, $data)
    {
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower(Str::remove(['\'', ','], $this->faker->unique()->company())));

        Config::set(Str::slug($service->getId()) . '.merchants.' . $data['id'], []);

        return new MerchantDto($service, $data);
    }

    protected function createMerchantDatabase($service, $data)
    {
        $data['service_id'] = $service->getId();

        return MerchantModel::factory()->create($data);
    }

    protected function linkMerchantToProvider(Merchantable $merchant, Providable $provider, $data = [])
    {
        $linkMerchantToProvider = 'linkMerchantToProvider' . Str::studly(Config::get('orchestration.defaults.driver'));

        $this->$linkMerchantToProvider($merchant, $provider, $data);
    }

    protected function linkMerchantToProviderConfig(Merchantable $merchant, Providable $provider, $data)
    {
        Config::set(
            $providers = Str::slug($merchant->getService()->getId()) . '.merchants.' . $merchant->getId() . '.providers',
            array_merge(
                Config::get($providers, []),
                [$provider->getId() => $data]
            )
        );
    }

    protected function linkMerchantToProviderDatabase(Merchantable $merchant, Providable $provider, $data)
    {
        $merchant->providers()->sync([$provider->getId() => $data], false);
    }
}
