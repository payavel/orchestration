<?php

namespace Payavel\Orchestration\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Fluent\FluentConfig;
use Payavel\Orchestration\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceConfigTest extends TestCase
{
    #[Test]
    public function set_service_config_in_separate_config()
    {
        Config::set('orchestration.services.mock', 'fake');

        $serviceConfig = FluentConfig::find('mock');

        $serviceConfig->set('assert', true);

        $this->assertTrue($serviceConfig->get('assert'));
        $this->assertTrue(Config::get('fake.assert'));
    }

    #[Test]
    public function set_service_config_in_orchestration_config()
    {
        Config::set('orchestration.services.mock', []);

        $serviceConfig = FluentConfig::find('mock');

        $serviceConfig->set('assert', true);

        $this->assertTrue($serviceConfig->get('assert'));
        $this->assertTrue(Config::get('orchestration.services.mock.assert'));
    }
}
