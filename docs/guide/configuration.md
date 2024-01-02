# Configuration

Orchestration relies heavily on config, especially when using the config driver,
but for now let's focus on the essential configurations.


## Orchestration Config
After running the `service:install` command, you should see a new config file in
`config/orchestration.php`. This file will serve as a global config file for all your services,
you can look at it as the default configuration for your services to be used as a fallback if
they haven't specified a service-level configuration.

Out of the box, this config looks like this:

```php
return [

    'services' => [
    
        'your_service' => [
            'config' => 'your-service',
        ],
        
    ],

];
```

But orchestration will merge the following config by default:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Orchestration Defaults
    |--------------------------------------------------------------------------
    |
    | This option determines the default orchestration settings for your application.
    | It is recommended to use the config driver over the database driver unless
    | you onboard new services, providers and/or merchants automatically.
    |
    */
    'defaults' => [
        'driver' => 'config',
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Drivers
    |--------------------------------------------------------------------------
    |
    | You may define & register custom service drivers for your application or
    | leverage any existing one. In order for the driver to be compatible
    | it must extend the \Payavel\Orchestration\ServiceDriver::class.
    |
    */
    'drivers' => [
        'config' => \Payavel\Orchestration\Drivers\ConfigDriver::class,
        'database' => \Payavel\Orchestration\Drivers\DatabaseDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Orchestration Test Mode
    |--------------------------------------------------------------------------
    |
    | When set to true, the provider & merchant will be shared with the respective
    | fake service request, this allows you to mock your responses as you wish.
    | Note that if the service defines the test_mode, it will be prioritized.
    |
    */
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
Please be aware that if you decide to register a custom driver, and wish to utilize any of the standard
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


## Service Level Config
The service level config is specific to the actual service. Any config set in this file will override the
orchestration config whenever both levels have the same config.

E.g. your orchestration.php config might look like this:
```php
return [

    'services' => [
    
        'checkout' => [
            'config' => 'checkout',
        ],
        
        'subscription' => [
            'config' => 'subscription',
        ],
    
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

Since the checkout's `test_mode` overrides the orchestration's `test_mode`, all calls made to the checkout
service will pass through the actual provider's gateway. And as long as the subscription service does not
define a `test_mode`, each call to the service will be forwarded to it's fake implementation.

### Defaults
Within the service defaults you may register the provider, merchant & driver. These will be injected into the
service's gateway if you do not explicitly set the provider & merchant prior to making a call to the gateway.

```php
return [
    
    'defaults' => [
        'driver' => 'config',
        'provider' => 'adyen',
        'merchant' => 'payavel',
    ],
    
];
```

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
