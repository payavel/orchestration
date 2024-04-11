<?php

namespace Payavel\Orchestration\Tests\Traits;

use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Models\Account;
use Payavel\Orchestration\Models\Provider;

trait CreatesDatabaseServiceables
{
    /**
     * Creates a providable instance.
     *
     * @param Serviceable|null $service
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function createProvider(Serviceable $service = null, $data = [])
    {
        if (is_null($service)) {
            $service = $this->createService();
        }

        $data['service_id'] = $service->getId();

        return Provider::factory()->create($data);
    }

    /**
     * Creates a accountable instance.
     *
     * @param Serviceable|null $service
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function createAccount(Serviceable $service = null, $data = [])
    {
        if (is_null($service)) {
            $service = $this->createService();
        }

        $data['service_id'] = $service->getId();

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
