<?php

namespace Payavel\Orchestration\Tests\Unit;

use BadMethodCallException;
use Exception;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\ServiceResponse;
use Payavel\Orchestration\Support\ServiceConfig;
use Payavel\Orchestration\Tests\Contracts\CreatesServiceables;
use Payavel\Orchestration\Tests\Services\Mock\FakeMockRequest;
use Payavel\Orchestration\Tests\Services\Mock\TestMockRequest;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Tests\Traits\CreatesServices;
use PHPUnit\Framework\Attributes\Test;

abstract class TestService extends TestCase implements CreatesServiceables
{
    use CreatesServices;

    private $serviceable;
    private $providable;
    private $merchantable;

    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceable = $this->createService([
            'id' => 'mock',
            'test_gateway' => FakeMockRequest::class,
        ]);

        $this->providable = $this->createProvider($this->serviceable, [
            'id' => 'test',
            'gateway' => TestMockRequest::class,
        ]);

        $this->merchantable = $this->createMerchant($this->serviceable, [
            'id' => 'x',
        ]);

        $this->linkMerchantToProvider($this->merchantable, $this->providable);

        $this->setDefaultsForService($this->serviceable, $this->merchantable, $this->providable);

        $this->service = new Service($this->serviceable);
    }

    public function assertRealIsAlignedWithFake(callable $test)
    {
        ServiceConfig::set($this->serviceable, 'test_mode', false);

        $test();

        ServiceConfig::set($this->serviceable, 'test_mode', true);

        $this->service->reset();

        $test();
    }

    #[Test]
    public function set_provider_and_merchant_fluently()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $response = $this->service
            ->provider($this->providable)
            ->merchant($this->merchantable)
            ->getIdentity();

            $this->assertEquals(
                ServiceConfig::get($this->serviceable, 'test_mode')
                    ? 'Fake'
                    : 'Real',
                $response->data
            );

            $this->assertResponseIsConfigured($response);
        });
    }

    #[Test]
    public function setting_invalid_driver_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            ServiceConfig::set($this->serviceable, 'defaults.driver', 'invalid');

            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Invalid driver provided.');

            new Service($this->serviceable);
        });
    }

    #[Test]
    public function setting_invalid_provider_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Invalid provider.');

            $this->service->setProvider('invalid');
        });
    }

    #[Test]
    public function setting_invalid_merchant_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Invalid merchant.');

            $this->service->setMerchant('invalid');
        });
    }

    #[Test]
    public function setting_incompatible_merchant_provider_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $incompatibleMerchant = $this->createMerchant($this->serviceable);

            $this->expectException(Exception::class);
            $this->expectExceptionMessage('The ' . $incompatibleMerchant->getName() . ' merchant is not supported by the ' . $this->providable->getName() . ' provider.');

            $this->service->provider($this->providable)->merchant($incompatibleMerchant)->getIdentity();
        });
    }

    #[Test]
    public function resetting_service_to_default_configuration()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $alternativeMerchant = $this->createMerchant($this->serviceable);
            $this->linkMerchantToProvider($alternativeMerchant, $this->providable);

            $this->service->provider($this->providable)->merchant($alternativeMerchant);

            $this->assertEquals($alternativeMerchant->getId(), $this->service->getIdentity()->merchant->getId());

            $this->service->reset();

            $this->assertEquals($this->merchantable->getId(), $this->service->getIdentity()->merchant->getId());
        });
    }

    #[Test]
    public function calling_undefined_method_on_service_throws_bad_method_call_exception()
    {
        $undefinedMethod = 'undefined';

        $this->assertRealIsAlignedWithFake(function () use ($undefinedMethod) {
            $this->expectException(BadMethodCallException::class);
            $this->expectExceptionMessage(get_class($this->service->gateway) . "::{$undefinedMethod}() not found.");

            $this->service->{$undefinedMethod}();
        });
    }

    #[Test]
    public function passing_additional_information_to_service_response()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $response = $this->service->getIdentity(true);

            $this->assertStringEndsWith('additional information', $response->data);
        });
    }

    protected function assertResponseIsConfigured(ServiceResponse $response)
    {
        $this->assertEquals($this->providable->getId(), $response->provider->getId());
        $this->assertEquals($this->merchantable->getId(), $response->merchant->getId());
    }
}
