<?php

namespace Payavel\Orchestration\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Payavel\Orchestration\Models\Account;
use Payavel\Orchestration\Service;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $account = Str::remove(['\'', ','], $this->faker->unique()->company());

        return [
            'id' => preg_replace('/[^a-z0-9]+/i', '_', strtolower($account)),
            'name' => $account,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Account $account) {
            if(is_null($account->service_id)) {
                $account->service_id = is_null($service = Service::all()->random())
                    ? Str::lower($this->faker->unique()->word())
                    : $service->getId();
            }
        });
    }
}
