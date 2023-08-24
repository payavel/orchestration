<?php

namespace Payavel\Serviceable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Payavel\Serviceable\Database\Factories\ProviderFactory;
use Payavel\Serviceable\Models\Provider;
use Payavel\Serviceable\ServiceableServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase, WithFaker;

    protected function getPackageProviders($app)
    {
        return [
            ServiceableServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'serviceable_test');
        $app['config']->set('database.connections.serviceable_test', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->afterApplicationRefreshedCallbacks = [
            fn () => $this->setUpDriver(),
        ];

        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    protected function setUpDriver()
    {
        if (
            ! isset($this->driver) ||
            ! method_exists($this, $setUp = 'setUp' . Str::Studly($this->driver))
        ) {
           return;
        }

        $this->$setUp();
    }

    protected function setUpConfig()
    {
        Config::set('serviceable.defaults.driver', 'config');
    }

    protected function setUpDatabase()
    {
        Config::set('serviceable.defaults.driver', 'database');
    }
}

class User extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];
    }
}
