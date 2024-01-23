<?php

namespace Payavel\Orchestration\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Payavel\Orchestration\Models\Provider;
use Payavel\Orchestration\Service;

class ProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Provider::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $provider = Str::remove(['\'', ','], $this->faker->unique()->company());

        return [
            'id' => preg_replace('/[^a-z0-9]+/i', '_', strtolower($provider)),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Provider $provider) {
            if(is_null($provider->service_id)) {
                $provider->service_id = is_null($service = Service::all()->random())
                    ? Str::lower($this->faker->unique()->word())
                    : $service->getId();
            }

            $studlyProvider = Str::studly($provider->id);
            $studlyService = Str::studly($provider->service_id);

            if (is_null($provider->gateway)) {
                $provider->gateway = "\\App\\Services\\{$studlyService}\\{$studlyProvider}{$studlyService}Request";
            }
        });
    }
}
