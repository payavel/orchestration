<?php

namespace Payavel\Orchestration;

use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;

abstract class ServiceRequest
{
    /**
     * The service provider.
     *
     * @var \Payavel\Orchestration\Contracts\Providable
     */
    protected $provider;

    /**
     * The service merchant.
     *
     * @var \Payavel\Orchestration\Contracts\Merchantable
     */
    protected $merchant;

    /**
     * @param  \Payavel\Orchestration\Contracts\Providable $provider
     * @param  \Payavel\Orchestration\Contracts\Merchantable $merchant
     */
    public function __construct(Providable $provider, Merchantable $merchant)
    {
        $this->provider = $provider;
        $this->merchant = $merchant;

        $this->setUp();
    }

    /**
     * Set up the request.
     *
     * @return void
     */
    protected function setUp()
    {
        //
    }
}
