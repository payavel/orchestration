<?php

namespace Payavel\Serviceable\Tests\Feature\Console\Commands;

use Payavel\Serviceable\Console\Commands\PublishStubs;
use Payavel\Serviceable\Tests\TestCase;

class PublishStubsCommandTest extends TestCase
{
    /** @test */
    public function publish_stubs_command_publishes_stubs()
    {
        $this->artisan('service:stubs')
            ->expectsOutput('Successfully published stubs!')
            ->assertExitCode(0);

        foreach(PublishStubs::$serviceableStubs as $stub) {
            $this->assertFileExists(base_path('stubs/serviceable/' . $stub . '.stub'));
        }
    }

    /** @test */
    public function publish_stubs_command_publishes_stubs_for_service()
    {
        $this->artisan('service:stubs', [
            '--service' => 'mock',
        ])
            ->expectsOutput('Successfully published stubs!')
            ->assertExitCode(0);

        foreach(PublishStubs::$serviceStubs as $stub) {
            $this->assertFileExists(base_path('stubs/serviceable/mock/' . $stub . '.stub'));
        }
    }

    /** @test */
    public function publish_stubs_command_publishes_single_stub_file()
    {
        $this->artisan('service:stubs', [
            'stub' => $stub = $this->faker->randomElement(PublishStubs::$serviceableStubs),
        ])
            ->expectsOutput('Successfully published stub!')
            ->assertExitCode(0);

        $this->assertFileExists(base_path('stubs/serviceable/' . $stub . '.stub'));
    }

    /** @test */
    public function publish_stubs_command_throws_error_when_single_stub_file_does_not_exist()
    {
        $this->artisan('service:stubs', [
            'stub' => 'stub',
        ])
            ->expectsOutput('The stub file you wish to publish is not available.')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(base_path('stubs/serviceable/stub.stub'));
    }
}
