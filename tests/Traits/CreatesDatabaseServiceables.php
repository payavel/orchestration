<?php

namespace Payavel\Orchestration\Tests\Traits;

use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\FluentConfig;
use Payavel\Orchestration\Models\Account;
use Payavel\Orchestration\Models\Provider;

trait CreatesDatabaseServiceables
{
    /**
     * Creates a providable instance.
     *
     * @param \Payavel\Orchestration\Fluent\FluentConfig $serviceConfig
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function createProvider(FluentConfig $serviceConfig, $data = [])
    {
        $data['service_id'] = $serviceConfig->id;

        return Provider::factory()->create($data);
    }

    /**
     * Creates a accountable instance.
     *
     * @param \Payavel\Orchestration\Fluent\FluentConfig $serviceConfig
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function createAccount(FluentConfig $serviceConfig, $data = [])
    {
        $data['service_id'] = $serviceConfig->id;

        return Account::factory()->create($data);
    }

    /**
     * Links a accountable instance to a providable one.
     *
     * @param Accountable $account
     * @param Providable $provider
     * @param array $data
     * @return void
     */
    public function linkAccountToProvider(Accountable $account, Providable $provider, $data = [])
    {
        throw_unless($account instanceof Account);

        $account->providers()->sync([$provider->getId() => $data], false);
    }
}
