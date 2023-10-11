<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Console\Commands\PublishStubs;
use Payavel\Orchestration\Tests\TestCase;

class PublishStubsCommandTest extends TestCase
{
    /** @test */
    public function publish_stubs_command_publishes_stubs()
    {
        $this->artisan('service:stubs')
            ->expectsOutput('Successfully published stubs!')
            ->assertExitCode(0);

        foreach(PublishStubs::$baseStubs as $stub) {
            $this->assertFileExists(base_path('stubs/orchestration/' . $stub . '.stub'));
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

        foreach(PublishStubs::$serviceSpecificStubs as $stub) {
            $this->assertFileExists(base_path('stubs/orchestration/mock/' . $stub . '.stub'));
        }
    }

    /** @test */
    public function publish_stubs_command_publishes_single_stub_file()
    {
        $this->artisan('service:stubs', [
            'stub' => $stub = $this->faker->randomElement(PublishStubs::$baseStubs),
        ])
            ->expectsOutput('Successfully published stub!')
            ->assertExitCode(0);

        $this->assertFileExists(base_path('stubs/orchestration/' . $stub . '.stub'));
    }

    /** @test */
    public function publish_stubs_command_throws_error_when_single_stub_file_does_not_exist()
    {
        $this->artisan('service:stubs', [
            'stub' => 'stub',
        ])
            ->expectsOutput('The stub file you wish to publish is not available.')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(base_path('stubs/orchestration/stub.stub'));
    }
}
