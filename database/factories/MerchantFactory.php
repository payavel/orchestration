<?php

namespace Payavel\Serviceable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Payavel\Serviceable\Models\Merchant;
use Payavel\Serviceable\Models\Provider;
use Payavel\Serviceable\Models\Service;

class MerchantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Merchant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $merchant = Str::remove(['\'', ','], $this->faker->unique()->company());

        return [
            'id' => preg_replace('/[^a-z0-9]+/i', '_', strtolower($merchant)),
            'name' => $merchant,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Merchant $merchant) {
            if(is_null($merchant->service_id)) {
                $service = Service::inRandomOrder()->firstOr(
                    fn () => Service::factory()->create()
                );

                $merchant->service_id = $service->id;
            }
        });
    }
}
