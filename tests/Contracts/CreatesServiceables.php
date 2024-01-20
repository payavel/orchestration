<?php

namespace Payavel\Orchestration\Tests\Contracts;

use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;

interface CreatesServiceables
{
    /**
     * Creates a serviceable instance.
     *
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function createService($data = []);

    /**
     * Creates a providable instance.
     *
     * @param Serviceable|null $service
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function createProvider(Serviceable $service = null, $data = []);

    /**
     * Creates a merchantable instance.
     *
     * @param Serviceable|null $service
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Merchantable
     */
    public function createMerchant(Serviceable $service = null, $data = []);

    /**
     * Links a merchantable instance to a providable one.
     *
     * @param Merchantable $merchant
     * @param Providable $provider
     * @param array $data
     * @return void
     */
    public function linkMerchantToProvider(Merchantable $merchant, Providable $provider, $data = []);

    /**
     * Sets the default configuration for a serviceable instance.
     *
     * @param Serviceable $service
     * @param Merchantable|null $merchant
     * @param Providable|null $provider
     * @return void
     */
    public function setDefaultsForService(Serviceable $service, Merchantable $merchant = null, Providable $provider = null);
}
