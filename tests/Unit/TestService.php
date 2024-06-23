<?php

namespace Payavel\Orchestration\Tests\Unit;

use BadMethodCallException;
use Exception;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\ServiceResponse;
use Payavel\Orchestration\Tests\Contracts\CreatesServiceables;
use Payavel\Orchestration\Tests\Services\Mock\FakeMockRequest;
use Payavel\Orchestration\Tests\Services\Mock\TestMockRequest;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Tests\Traits\CreatesServices;
use PHPUnit\Framework\Attributes\Test;

abstract class TestService extends TestCase implements CreatesServiceables
{
    use CreatesServices;

    private $serviceConfig;
    private $providable;
    private $accountable;

    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceConfig = $this->createServiceConfig([
            'id' => 'mock',
            'test_gateway' => FakeMockRequest::class,
        ]);

        $this->providable = $this->createProvider($this->serviceConfig, [
            'id' => 'test',
            'gateway' => TestMockRequest::class,
        ]);

        $this->accountable = $this->createAccount($this->serviceConfig, [
            'id' => 'x',
        ]);

        $this->linkAccountToProvider($this->accountable, $this->providable);

        $this->setDefaultsForService($this->serviceConfig, $this->accountable, $this->providable);

        $this->service = new Service($this->serviceConfig);
    }

    public function assertRealIsAlignedWithFake(callable $test)
    {
        $this->serviceConfig->set('test_mode', false);

        $test();

        $this->serviceConfig->set('test_mode', true);

        $this->service->reset();

        $test();
    }

    #[Test]
    public function set_provider_and_account_fluently()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $response = $this->service
            ->provider($this->providable)
            ->account($this->accountable)
            ->getIdentity();

            $this->assertEquals(
                $this->serviceConfig->get('test_mode')
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
            $this->serviceConfig->set('defaults.driver', 'invalid');

            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Invalid driver provided.');

            new Service($this->serviceConfig);
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
    public function setting_invalid_account_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('Invalid account.');

            $this->service->setAccount('invalid');
        });
    }

    #[Test]
    public function setting_incompatible_account_provider_throws_exception()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $incompatibleAccount = $this->createAccount($this->serviceConfig);

            $this->expectException(Exception::class);
            $this->expectExceptionMessage('The '.$incompatibleAccount->getName().' account is not supported by the '.$this->providable->getName().' provider.');

            $this->service->provider($this->providable)->account($incompatibleAccount)->getIdentity();
        });
    }

    #[Test]
    public function resetting_service_to_default_configuration()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $alternativeAccount = $this->createAccount($this->serviceConfig);
            $this->linkAccountToProvider($alternativeAccount, $this->providable);

            $this->service->provider($this->providable)->account($alternativeAccount);

            $this->assertEquals($alternativeAccount->getId(), $this->service->getIdentity()->account->getId());

            $this->service->reset();

            $this->assertEquals($this->accountable->getId(), $this->service->getIdentity()->account->getId());
        });
    }

    #[Test]
    public function calling_undefined_method_on_service_throws_bad_method_call_exception()
    {
        $undefinedMethod = 'undefined';

        $this->assertRealIsAlignedWithFake(function () use ($undefinedMethod) {
            $this->expectException(BadMethodCallException::class);
            $this->expectExceptionMessage(get_class($this->service->gateway)."::{$undefinedMethod}() not found.");

            $this->service->{$undefinedMethod}();
        });
    }

    #[Test]
    public function passing_additional_information_to_service_response()
    {
        $this->assertRealIsAlignedWithFake(function () {
            $response = $this->service->getIdentity(true);

            $this->assertStringEndsWith('additional data', $response->data);
        });
    }

    protected function assertResponseIsConfigured(ServiceResponse $response)
    {
        $this->assertEquals($this->providable->getId(), $response->provider->getId());
        $this->assertEquals($this->accountable->getId(), $response->account->getId());
    }
}
