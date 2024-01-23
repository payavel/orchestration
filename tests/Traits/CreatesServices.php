<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\DataTransferObjects\Service;

trait CreatesServices
{
    /**
     * Creates a serviceable instance.
     *
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function createService($data = [])
    {
        $data['id'] = $data['id'] ?? Str::lower($this->faker->unique()->word());
        $data['test_gateway'] = $data['test_gateway'] ?? '\\App\\Services\\' . Str::studly($data['id']) . '\\Fake' . Str::studly($data['id']) . 'Request';

        Config::set('orchestration.services.' . $data['id'], $serviceSlug = Str::slug($data['id']));

        Config::set($serviceSlug . '.testing.gateway', $data['test_gateway']);

        return new Service($data);
    }

    /**
     * Sets the default configuration for a serviceable instance.
     *
     * @param Serviceable $service
     * @param Merchantable|null $merchant
     * @param Providable|null $provider
     * @return void
     */
    public function setDefaultsForService(Serviceable $service, Merchantable $merchant = null, Providable $provider = null)
    {
        Config::set(
            Str::slug($service->getId()) . '.defaults.merchant',
            $merchant instanceof Merchantable ? $merchant->getId() : $merchant
        );

        if (is_null($provider) && ! is_null($merchant)) {
            $provider = Collection::make(
                Config::get(Str::slug($service->getId()) . '.merchants.' . $merchant->getId() . '.providers')
            )
                ->keys()
                ->first();
        }

        Config::set(
            Str::slug($service->getId()) . '.defaults.provider',
            $provider instanceof Providable ? $provider->getId() : $provider
        );
    }
}
