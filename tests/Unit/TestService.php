<?php

namespace Payavel\Serviceable\Tests\Unit;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Serviceable\Service;
use Payavel\Serviceable\ServiceResponse;
use Payavel\Serviceable\Tests\Services\Mock\FakeMockRequest;
use Payavel\Serviceable\Tests\Services\Mock\FakeMockResponse;
use Payavel\Serviceable\Tests\Services\Mock\TestMockRequest;
use Payavel\Serviceable\Tests\Services\Mock\TestMockResponse;
use Payavel\Serviceable\Tests\TestCase;
use Payavel\Serviceable\Tests\Traits\CreatesServiceables;
use Payavel\Serviceable\Tests\Traits\SetsMode;

class TestService extends TestCase
{
    use CreatesServiceables,
        SetsMode;

    private $serviceable;
    private $providable;
    private $merchantable;

    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceable = $this->createService([
            'id' => 'mock',
        ]);

        $this->providable = $this->createProvider($this->serviceable, [
            'id' => 'test',
            'request_class' => TestMockRequest::class,
            'response_class' => TestMockResponse::class,
        ]);

        $this->merchantable = $this->createMerchant($this->serviceable, [
            'id' => 'x',
        ]);

        $this->linkMerchantToProvider($this->merchantable, $this->providable);

        Config::set(Str::slug($this->serviceable->getId()) . '.defaults.provider', $this->providable->getId());
        Config::set(Str::slug($this->serviceable->getId()) . '.defaults.merchant', $this->merchantable->getId());
        Config::set(Str::slug($this->serviceable->getId()) . '.testing', [
            'request_class' => FakeMockRequest::class,
            'response_class' => FakeMockResponse::class,
        ]);

        $this->setMode($this->serviceable);

        $this->service = new Service($this->serviceable);
    }

    /** @test */
    public function set_provider_and_merchant_fluently()
    {
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
    }

    /** @test */
    public function setting_invalid_driver_throws_exception()
    {
        Config::set(Str::slug($this->serviceable->getId()) . '.defaults.driver', 'invalid');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid driver provided.');

        $service = new Service($this->serviceable);
    }

    /** @test */
    public function setting_invalid_provider_throws_exception()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid provider.');

        $this->service->setProvider('invalid');
    }

    /** @test */
    public function setting_invalid_merchant_throws_exception()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid merchant.');

        $this->service->setMerchant('invalid');
    }

    /** @test */
    public function setting_incompatible_merchant_provider_throws_exception()
    {
        $incompatibleMerchant = $this->createMerchant($this->serviceable);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The ' . $incompatibleMerchant->getName() . ' merchant is not supported by the ' . $this->providable->getName() . ' provider.');

        $this->service->provider($this->providable)->merchant($incompatibleMerchant)->getIdentity();
    }

    /** @test */
    public function resetting_service_to_default_configuration()
    {
        $alternativeMerchant = $this->createMerchant($this->serviceable);
        $this->linkMerchantToProvider($alternativeMerchant, $this->providable);

        $this->service->provider($this->providable)->merchant($alternativeMerchant);

        $this->assertEquals($alternativeMerchant->getId(), $this->service->getIdentity()->merchant->getId());

        $this->service->reset();

        $this->assertEquals($this->merchantable->getId(), $this->service->getIdentity()->merchant->getId());
    }

    protected function assertResponseIsConfigured(ServiceResponse $response)
    {
        $this->assertEquals($this->providable->getId(), $response->provider->getId());
        $this->assertEquals($this->merchantable->getId(), $response->merchant->getId());
    }
}
