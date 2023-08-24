<?php

namespace Payavel\Serviceable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Payavel\Serviceable\Models\Provider;
use Payavel\Serviceable\Models\Service;

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
        $id = preg_replace('/[^a-z0-9]+/i', '_', strtolower($provider));

        return [
            'id' => $id,
            'name' => $provider,
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
                $service = Service::inRandomOrder()->firstOr(function () {
                    return Service::factory()->create();
                });

                $provider->service_id = $service->id;
            }

            $studlyProvider = Str::studly($provider->id);
            $studlyService = Str::studly($provider->service_id);

            if (is_null($provider->request_class)) {
                $provider->request_class = "\App\Services\{$studlyService}\{$studlyProvider}{$studlyService}Request";
            }

            if (is_null($provider->response_class)) {
                $provider->response_class = "\App\Services\{$studlyService}\{$studlyProvider}{$studlyService}Response";
            }
        });
    }
}
