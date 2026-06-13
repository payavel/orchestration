<?php

namespace Payavel\Orchestration\Tests\Unit\Database;

use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;
use Payavel\Orchestration\Tests\Traits\CreatesServices;
use Payavel\Orchestration\Tests\Traits\SetsDatabaseDriver;
use PHPUnit\Framework\Attributes\Test;

class AccountProviderConfigTest extends TestCase
{
    use CreatesServices;
    use CreatesDatabaseServiceables;
    use SetsDatabaseDriver;

    #[Test]
    public function set_account_provider_config_with_encrypted_values_and_get_decrypted_values()
    {
        $serviceConfig = $this->createServiceConfig();
        $provider = $this->createProvider($serviceConfig);
        $account = $this->createAccount($serviceConfig);
        $config = [
            'example' => 'decrypted value',
            'api_key' => 'encrypted value',
        ];

        $account->setConfig(
            $provider,
            $config,
            ['api_key', 'secret_key']
        );

        $this->assertNotSame(
            $config,
            json_decode($account->providers()->where('provider_id', $provider->getId())->first()->pivot->config, true)
        );
        $this->assertSame(
            $config,
            $account->getConfig($provider)
        );

        $this->assertEquals(
            $config['example'],
            json_decode($account->providers()->where('provider_id', $provider->getId())->first()->pivot->config, true)['example']
        );
        $this->assertEquals(
            $config['example'],
            $account->getConfig($provider)['example']
        );

        $this->assertNotEquals(
            $config['api_key'],
            json_decode($account->providers()->where('provider_id', $provider->getId())->first()->pivot->config, true)['api_key']
        );
        $this->assertEquals(
            $config['api_key'],
            $account->getConfig($provider)['api_key']
        );
    }
}
