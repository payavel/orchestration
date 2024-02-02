<?php

namespace Payavel\Orchestration\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Support\ServiceConfig;

class ServiceConfigTest extends TestCase
{
    /** @test */
    public function set_service_config_in_separate_config()
    {
        Config::set('orchestration.services.mock', 'mock');

        Config::set('mock', [
            'assert' => true,
        ]);

        $this->assertTrue(ServiceConfig::get(Service::find('mock'), 'assert'));
        $this->assertTrue(ServiceConfig::get('mock', 'assert'));
    }

    /** @test */
    public function set_service_config_in_orchestration_config()
    {
        Config::set('orchestration.services.mock', [
            'assert' => true,
        ]);

        $this->assertTrue(ServiceConfig::get(Service::find('mock'), 'assert'));
        $this->assertTrue(ServiceConfig::get('mock', 'assert'));
    }
}
