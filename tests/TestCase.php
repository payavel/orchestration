<?php

namespace Payavel\Serviceable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;
use Payavel\Serviceable\ServiceableServiceProvider;
use Payavel\Serviceable\Tests\Traits\SetUpDriver;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use SetUpDriver,
        RefreshDatabase,
        WithFaker;

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

    protected function tearDown(): void
    {
        if (file_exists($config = config_path('serviceable.php'))) {
            unlink($config);
        }

        parent::tearDown();
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
