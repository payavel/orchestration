<?php

namespace Payavel\Orchestration\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;
use Payavel\Orchestration\OrchestrationServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function getPackageProviders($app)
    {
        return [
            OrchestrationServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'orchestration_test');
        $app['config']->set('database.connections.orchestration_test', [
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
            fn () => $this->setDriver(),
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
        if (file_exists($config = config_path('orchestration.php'))) {
            unlink($config);
        }

        foreach (glob(database_path('migrations/*_add_providers_and_accounts_to_*_service.php')) as $migration) {
            unlink($migration);
        }

        parent::tearDown();
    }

    /**
     * Set the driver.
     *
     * @return void
     */
    protected function setDriver()
    {
        //
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
