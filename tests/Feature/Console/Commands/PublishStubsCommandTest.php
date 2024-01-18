<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Console\Commands\OrchestrateStubs;
use Payavel\Orchestration\Tests\TestCase;

class OrchestrateStubsCommandTest extends TestCase
{
    /** @test */
    public function publish_stubs_command_publishes_stubs()
    {
        $this->artisan('orchestrate:stubs')
            ->expectsOutput('Successfully published stubs!')
            ->assertExitCode(0);

        foreach(OrchestrateStubs::$baseStubs as $stub) {
            $this->assertFileExists(base_path('stubs/orchestration/' . $stub . '.stub'));
        }
    }

    /** @test */
    public function publish_stubs_command_publishes_stubs_for_service()
    {
        $this->artisan('orchestrate:stubs', [
            '--service' => 'mock',
        ])
            ->expectsOutput('Successfully published stubs!')
            ->assertExitCode(0);

        foreach(OrchestrateStubs::$serviceSpecificStubs as $stub) {
            $this->assertFileExists(base_path('stubs/orchestration/mock/' . $stub . '.stub'));
        }
    }

    /** @test */
    public function publish_stubs_command_publishes_single_stub_file()
    {
        $this->artisan('orchestrate:stubs', [
            'stub' => $stub = $this->faker->randomElement(OrchestrateStubs::$baseStubs),
        ])
            ->expectsOutput('Successfully published stub!')
            ->assertExitCode(0);

        $this->assertFileExists(base_path('stubs/orchestration/' . $stub . '.stub'));
    }

    /** @test */
    public function publish_stubs_command_throws_error_when_single_stub_file_does_not_exist()
    {
        $this->artisan('orchestrate:stubs', [
            'stub' => 'stub',
        ])
            ->expectsOutput('The stub file you wish to publish is not available.')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(base_path('stubs/orchestration/stub.stub'));
    }
}
