<?php

namespace Payavel\Orchestration\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Payavel\Orchestration\Models\Service;

class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::lower($this->faker->unique()->word()),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Service $service) {
            if (! is_null($service->test_gateway)) {
                return;
            }

            $studlyService = Str::studly($service->id);

            $service->test_gateway = "\App\Services\{$studlyService}\Fake{$studlyService}Request";
        });
    }
}
