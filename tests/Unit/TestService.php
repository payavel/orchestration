<?php

namespace Payavel\Serviceable\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Payavel\Serviceable\Service;
use Payavel\Serviceable\ServiceResponse;
use Payavel\Serviceable\Tests\Services\Mock\FakeMockRequest;
use Payavel\Serviceable\Tests\Services\Mock\FakeMockResponse;
use Payavel\Serviceable\Tests\Services\Mock\TestMockRequest;
use Payavel\Serviceable\Tests\Services\Mock\TestMockResponse;
use Payavel\Serviceable\Tests\TestCase;
use Payavel\Serviceable\Tests\Traits\CreateServiceables;
use Payavel\Serviceable\Tests\Traits\SetUpMode;

class TestService extends TestCase
{
    use CreateServiceables,
        SetUpMode;

    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $service = $this->createService([
            'id' => 'mock',
            'name' => 'Mock',
        ]);

        $provider = $this->createProvider($service, [
            'id' => 'test',
            'name' => 'Test',
            'request_class' => TestMockRequest::class,
            'response_class' => TestMockResponse::class,
        ]);

        $merchant = $this->createMerchant($service, [
            'id' => 'x',
            'name' => 'X',
        ]);

        $this->linkMerchantToProvider($merchant, $provider);

        Config::set('mock.mock', [
            'request_class' => FakeMockRequest::class,
            'response_class' => FakeMockResponse::class,
        ]);

        $this->setUpMode($service);

        $this->service = new Service($service);
    }
}
