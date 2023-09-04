<?php

namespace Payavel\Serviceable\Tests\Feature\Console\Commands;

use Illuminate\Support\Str;
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
            $this->assertTrue(file_exists(base_path('stubs/serviceable/' . $stub . '.stub')));
        }
    }
}
