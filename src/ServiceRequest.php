<?php

namespace Payavel\Orchestration;

use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Illuminate\Support\Str;

abstract class ServiceRequest
{
    /**
     * The service response class.
     *
     * @var \Payavel\Orchestration\ServiceResponse
     */
    protected ServiceResponse $serviceResponse;

    /**
     * The service provider.
     *
     * @var \Payavel\Orchestration\Contracts\Providable
     */
    protected Providable $provider;

    /**
     * The service account.
     *
     * @var \Payavel\Orchestration\Contracts\Accountable
     */
    protected Accountable $account;

    public function __construct(Providable $provider, Accountable $account)
    {
        $this->provider = $provider;
        $this->account = $account;

        $this->setUp();
    }

    /**
     * Sets up the request.
     *
     * @return void
     */
    protected function setUp(): void
    {
        //
    }

    /**
     * Makes the request.
     *
     * @param string $method
     * @param array $params
     *
     * @return \Payavel\Orchestration\ServiceResponse|mixed
     *
     * @throws \BadMethodCallException
     */
    public function request(string $method, array $params = []): mixed
    {
        if (! method_exists($this, $method)) {
            throw new \BadMethodCallException(get_class($this)."::{$method}() not found.");
        }

        $response = $this->{$method}(...$params);

        if (! $response instanceof ServiceResponse) {
            $response = $this->response($response);
        }

        return $response->configure($method, $this->provider, $this->account);
    }

    /**
     * Formats the response.
     *
     * @param mixed $rawResponse
     *
     * @return \Payavel\Orchestration\ServiceResponse
     */
    public function response(mixed $rawResponse): ServiceResponse
    {
        $serviceResponse = $this->serviceResponse ?? Str::replace('Request', 'Response', get_class($this));

        return new $serviceResponse($rawResponse);
    }
}
