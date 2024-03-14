# Responses
Just like requests made to your service, the response is determined by your applications rules, not the service provider. This is why each request expects you to return an instance of `Payavel\Orchestration\ServiceResponse`. This class helps you define your responses in a structured way.

## Preparing the response
You may update the `setUp()` method to mutate the raw response in a way it will be easier to manipulate and map your responses correctly.

```php
protected array $decodedResponse;

/**
 * Set up the response.
 *
 * @return void
 */
protected function setUp()
{
    $this->decodedResponse = json_decode($this->rawResponse, true);
}
```

## Structuring the response
By default, when you make a request towards your service, the name of that request method is injected into your response class so you can define the response in a function named after the request method and suffixed with `Response`.

When calling `authorize` on the checkout service you should implement your response logic in the `authorizeResponse` method of the `CheckoutResponse` class.

```php
$checkout = new Payavel\Orchestration\Service('checkout');

$response = $checkout->authorize([...]);

$response->data;
[
    ''
]
```

## Statuses
Even though it is not required to invest much time into defining your services statuses, it is highly recommended to do so in a way your application understands them and you can map your provider's statuses to your own.

### Success


### Failure
