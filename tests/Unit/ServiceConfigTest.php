<?php

namespace Payavel\Orchestration\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Support\ServiceConfig;
use PHPUnit\Framework\Attributes\Test;

class ServiceConfigTest extends TestCase
{
    #[Test]
    public function set_service_config_in_separate_config()
    {
        Config::set('orchestration.services.mock', 'fake');

        ServiceConfig::set('mock', 'assert', true);

        $this->assertTrue(ServiceConfig::get(Service::find('mock'), 'assert'));
        $this->assertTrue(ServiceConfig::get('mock', 'assert'));
        $this->assertTrue(Config::get('fake.assert'));
    }

    #[Test]
    public function set_service_config_in_orchestration_config()
    {
        Config::set('orchestration.services.mock', []);

        ServiceConfig::set('mock', 'assert', true);

        $this->assertTrue(ServiceConfig::get(Service::find('mock'), 'assert'));
        $this->assertTrue(ServiceConfig::get('mock', 'assert'));
        $this->assertTrue(Config::get('orchestration.services.mock.assert'));
    }
}
