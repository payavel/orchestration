# Configuration

Orchestration relies heavily on config, especially when using the config driver,
but for now let's focus on the essential configurations.


## Global Config
After running the `orchestrate:service` command the first time, you should see a new
config file in `config/orchestration.php`. This file will serve as a global config
file for all your services, you can look at it as the default configuration to be
used as a fallback if a service-specific configuration hasn't been provided.

Out of the box, this config looks like this:

```php
return [

    'defaults' => [
        'driver' => 'config',
    ],

    'services' => [

        'your_service' => [
            'config' => 'your-service',
        ],

    ],

];
```

You should also expect the following configurations by default.

```php
return [

    'drivers' => [
        'config' => \Payavel\Orchestration\Drivers\ConfigDriver::class,
        'database' => \Payavel\Orchestration\Drivers\DatabaseDriver::class,
    ],

    'test_mode' => env('ORCHESTRATION_TEST_MODE', false),

];
```

### Defaults
Within the orchestration defaults you may specify the driver that will be used to resolve the services
you have installed in your application.

```php
return [

    'defaults' => [
        'driver' => 'config',
    ],

];
```

### Drivers
If you wish to create your own driver to resolve your service's dependencies, you are encouraged to do so,
this is where that driver should be registered.

```php
return [

    'drivers' => [
        'config' => \Payavel\Orchestration\Drivers\ConfigDriver::class,
        'redis' => \App\Drivers\RedisDriver::class, // Custom driver.
    ],

];
```

::: warning :warning:
Be aware that if you decide to register a custom driver, and wish to utilize any of the standard
drivers for other services, don't forget to register them too, as this option will override the default
value.
:::

### Test Mode
By default, test mode is set to `false`, you can either change this by overriding the config or simply
specifying `ORCHESTRATION_TEST_MODE=true` in your `.env` file. You might want to turn test mode on as
a general rule when running tests or if many of your services do not offer a sandbox to connect to.
When turned on, orchestration will automatically inject your "Fake" service implementation where you
will have full control of how you mock your service.

```php
return [

    'test_mode' => env('ORCHESTRATION_TEST_MODE', false),

];
```

### Overriding Package Models
If you are using the database driver, and wish to add application logic to the preexisting models, you may
do so by specifying the model that you will be replacing.

```php
return [

    'models' => [
        Payavel\Orchestration\Models\Provider::class => App\Models\Provider::class,
        Payavel\Orchestration\Models\Merchant::class => App\Models\Merchant::class,
    ],

];
```


## Service Config
Any configuration set in the service specific config file will override the global config during that
service's execution.

E.g. your orchestration.php config may look like this:
```php
return [

    'services' => [

        'checkout' => 'checkout',

        'subscription' => 'subscription',

    ],

    'test_mode' => true,

];
```
While the checkout.php config looks like this:
```php
return [

    'test_mode' => false,

];
```

Since `checkout.test_mode` overrides orchestration's `orchestration.test_mode`, all calls made to the
checkout service will pass through the provider's actual gateway. And as long as you don't define
`subscription.test_mode`, each call to the service will be forwarded to it's fake gateway.


### Defaults
You may register the provider, merchant & driver configurations within the `defaults` of your service
specific config file. These will be injected into the service's gateway if you do not explicitly set
them prior to making use of the gateway.

```php
return [

    'defaults' => [
        'driver' => 'config',
        'provider' => 'adyen',
        'merchant' => 'payavel',
    ],

];
```

::: info :memo: Note
If you orchestrate multiple merchants for a service, it is recommended to not set a default merchant
as it may cause your application to make calls to the service provider on behalf of that merchant if you
accidentally fail to specify the merchant you are targeting to be used.
:::


### Testing
Here you should register the service's fake gateway that will mock your provider's response when in test mode.
```php
return [

    'test_gateway' => \App\Services\Checkout\FakeCheckoutRequest::class,

];
```

### Providers
This is where you can register each of your service's provider implementations. This is also a good place to
register any provider specific configurations.
```php
return [

    'providers' => [

        'adyen' => [
            'gateway' => \App\Services\Checkout\AdyenCheckoutRequest::class,
        ],

        'stripe' => [
            'gateway' => \App\Services\Checkout\StripeCheckoutRequest::class,
        ],

    ],

];
```

### Merchants
This is where you can register each merchant that will be leveraging your service via one or more supported providers.
You must also list all the providers the merchant will be using and their configurations.
```php
return [

    'payavel' => [
        'providers' => [
            'adyen' => [
                'api_key' => env('ADYEN_PAYAVEL_API_KEY'),
            ],
            'stripe' => [
                'api_key' => env('STRIPE_PAYAVEL_API_KEY'),
            ],
        ],
    ],

];
```
