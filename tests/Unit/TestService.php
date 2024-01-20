<?php

namespace Payavel\Orchestration\Tests\Unit;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\ServiceResponse;
use Payavel\Orchestration\Tests\Contracts\CreatesServiceables;
use Payavel\Orchestration\Tests\Services\Mock\FakeMockRequest;
use Payavel\Orchestration\Tests\Services\Mock\TestMockRequest;
use Payavel\Orchestration\Tests\TestCase;

abstract class TestService extends TestCase implements CreatesServiceables
{
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
        $config = Str::slug($this->serviceable->getId());

        Config::set($config . '.test_mode', false);

        $test();

        Config::set($config . '.test_mode', true);

        $this->service->reset();

        $test();
    }

    /** @test */
    public function set_provider_and_merchant_fluently()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $response = $this->service
            ->provider($this->providable)
            ->merchant($this->merchantable)
            ->getIdentity();

            $this->assertEquals(
                Config::get(Str::slug($this->serviceable->getId()) . '.test_mode')
                    ? 'Fake'
                    : 'Real',
                $response->data
            );

            $this->assertResponseIsConfigured($response);
        });
    }

    /** @test */
    public function setting_invalid_driver_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            Config::set(Str::slug($this->serviceable->getId()) . '.defaults.driver', 'invalid');

            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Invalid driver provided.');

            $service = new Service($this->serviceable);
        });
    }

    /** @test */
    public function setting_invalid_provider_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Invalid provider.');

            $this->service->setProvider('invalid');
        });
    }

    /** @test */
    public function setting_invalid_merchant_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Invalid merchant.');

            $this->service->setMerchant('invalid');
        });
    }

    /** @test */
    public function setting_incompatible_merchant_provider_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $incompatibleMerchant = $this->createMerchant($this->serviceable);

            $this->expectException(Exception::class);
            $this->expectExceptionMessage('The ' . $incompatibleMerchant->getName() . ' merchant is not supported by the ' . $this->providable->getName() . ' provider.');

            $this->service->provider($this->providable)->merchant($incompatibleMerchant)->getIdentity();
        });
    }

    /** @test */
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

    protected function assertResponseIsConfigured(ServiceResponse $response)
    {
        $this->assertEquals($this->providable->getId(), $response->provider->getId());
        $this->assertEquals($this->merchantable->getId(), $response->merchant->getId());
    }
}
