<?php

namespace Payavel\Orchestration;

use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Illuminate\Support\Str;

abstract class ServiceRequest
{
    /**
     * The service response class.
     *
     * @var \Payavel\Orchestration\ServiceResponse
     */
    protected $serviceResponse;

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

    /**
     * Make the request.
     *
     * @param string $method
     * @param array|null $params
     *
     * @return \Payavel\Orchestration\ServiceResponse|mixed
     *
     * @throws \BadMethodCallException
     */
    public function request($method, $params)
    {
        if (! method_exists($this, $method)) {
            throw new \BadMethodCallException(get_class($this) . "::{$method}() not found.");
        }

        $response = $this->{$method}(...$params);

        if (! $response instanceof ServiceResponse) {
            $response = $this->response($response);
        }

        return $response->configure($method, $this->provider, $this->merchant);
    }

    /**
     * Format the response.
     *
     * @param mixed $rawResponse
     *
     * @return \Payavel\Orchestration\ServiceResponse
     */
    public function response($rawResponse)
    {
        $serviceResponse = $this->serviceResponse ?? Str::replace('Request', 'Response', self::class);

        return new $serviceResponse($rawResponse);
    }
}
