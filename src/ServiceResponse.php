<?php

namespace Payavel\Orchestration;

use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Traits\SimulatesAttributes;
use Payavel\Orchestration\Traits\ThrowsRuntimeException;

abstract class ServiceResponse
{
    use SimulatesAttributes;
    use ThrowsRuntimeException;

    /**
     * Statuses in this array are considered successful.
     *
     * @var array
     */
    protected array $successStatuses = [];

    /**
     * Custom response method names of the requests.
     *
     * @var array
     */
    protected array $responseMethods = [];

    /**
     * The provider's raw response.
     *
     * @var mixed
     */
    protected mixed $rawResponse;

    /**
     * Additional data needed to format the response.
     *
     * @var mixed
     */
    protected mixed $additionalData;

    /**
     * The request method that returned this response.
     *
     * @var string
     */
    public string $requestMethod;

    /**
     * The provider that the request was made towards.
     *
     * @var \Payavel\Orchestration\Contracts\Providable
     */
    public $provider;

    /**
     * The account that was used to make the request.
     *
     * @var \Payavel\Orchestration\Contracts\Accountable
     */
    public Accountable $account;

    /**
     * The expected formatted data based on the $request.
     *
     * @var mixed
     */
    private mixed $data;

    public function __construct(mixed $rawResponse)
    {
        $this->rawResponse = $rawResponse;

        $this->setUp();
    }

    /**
     * Sets up the response.
     *
     * @return void
     */
    protected function setUp(): void
    {
        //
    }

    /**
     * Shares additional data.
     *
     * @param mixed $additionalData
     *
     * @return static
     */
    public function with(mixed $additionalData): static
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * Configures the response based on the request.
     *
     * @param string $requestMethod
     * @param \Payavel\Orchestration\Contracts\Providable $provider
     * @param \Payavel\Orchestration\Contracts\Accountable $account
     *
     * @return \Payavel\Orchestration\ServiceResponse
     */
    public function configure(string $requestMethod, Providable $provider, Accountable $account): static
    {
        $this->requestMethod = $requestMethod;
        $this->provider = $provider;
        $this->account = $account;

        return $this;
    }

    /**
     * Gets the provider's raw response.
     *
     * @return mixed
     */
    public function getRawResponse(): mixed
    {
        return $this->rawResponse;
    }

    /**
     * Alias for the getRawResponse function.
     *
     * @return mixed
     */
    public function getRaw(): mixed
    {
        return $this->getRawResponse();
    }

    /**
     * Verifies whether the request should be considered successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return in_array($this->getStatusCode(), $this->successStatuses);
    }

    /**
     * Verifies whether the request should be considered a failure.
     *
     * @return bool
     */
    public function isNotSuccessful(): bool
    {
        return ! $this->isSuccessful();
    }

    /**
     * Gets the status code based on the request's raw response.
     *
     * @return int|string
     */
    abstract public function getStatusCode(): int|string;

    /**
     * Gets a string representation of the response's status.
     *
     * @return string|null
     */
    abstract public function getStatusMessage(): ?string;

    /**
     * Gets a description of the response's status.
     *
     * @return string|null
     */
    abstract public function getStatusDescription(): ?string;

    /**
     * Gets the formatted details based on the request that was made.
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function getData(): mixed
    {
        if (! isset($this->data)) {
            $this->data = $this->{$this->getResponseMethod()}();
        }

        return $this->data;
    }

    /**
     * Gets the response method that should be used to get the response's data.
     *
     * @return string
     */
    protected function getResponseMethod(): string
    {
        if (isset($this->requestMethod)) {
            if (
                array_key_exists($this->requestMethod, $this->responseMethods) &&
                method_exists($this, $method = $this->responseMethods[$this->requestMethod])
            ) {
                return $method;
            }

            if (method_exists($this, $method = "{$this->requestMethod}Response")) {
                return $method;
            }
        }

        return 'response';
    }

    /**
     * Defines the generic request response.
     *
     * @throws \RuntimeException
     */
    public function response()
    {
        return $this->throwRuntimeException(__FUNCTION__);
    }
}
