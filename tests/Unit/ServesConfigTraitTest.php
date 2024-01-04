<?php

namespace Payavel\Orchestration\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Traits\ServesConfig;

class ServesConfigTraitTest extends TestCase
{
    use ServesConfig;

    /** @test */
    public function set_service_config_in_separate_config()
    {
        Config::set('orchestration.services.mock', 'mock');

        Config::set('mock', [
            'assert' => true,
        ]);

        $this->assertTrue($this->config('mock', 'assert'));
    }

    /** @test */
    public function set_service_config_in_orchestration_config()
    {
        Config::set('orchestration.services.mock', [
            'assert' => true,
        ]);

        $this->assertTrue($this->config('mock', 'assert'));
    }
}