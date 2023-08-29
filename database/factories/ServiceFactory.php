<?php

namespace Payavel\Serviceable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Payavel\Serviceable\Models\Service;

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
        $service = $this->faker->unique()->word();

        return [
            'id' => Str::lower($service),
        ];
    }
}
