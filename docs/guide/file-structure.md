# File Structure

Your services will integrate smoothly with your Laravel application's directory structure
if you follow the framework's standard conventions.

```
.
├─ app
│  ├─ Services
│  │  ├─ Checkout
│  │  │  ├─ Contracts
│  │  │  │  ├─ CheckoutRequester.php
│  │  │  │  └─ CheckoutResponder.php
│  │  │  ├─ AdyenCheckoutRequest.php
│  │  │  ├─ AdyenCheckoutResponse.php
│  │  │  ├─ FakeCheckoutRequest.php
│  └─ └─ └─ FakeCheckoutResponse.php
├─ config
│  ├─ orchestration.php
└─ └─ checkout.php
```

## Services Directory
By default, the services directory will store all of the services you wish for your
application to orchestrate, they will each be nested within a subfolder that will include
the service's contracts, provider gateways & a single fake gateway.

### Contracts
After running the `orchestrate:service` command you will find that it has generated the
`ServiceRequester.php` & `ServiceResponder.php` interfaces respectively. While you might
find the interfaces to be empty, they are ready for you to start structuring your own
standard for to maintain all of your providers aligned through your application.

### Provider Gateways
Every time you run the `orchestrate:provider` command, you will find it generating
`ProviderServiceRequest.php` & `ProviderServiceResponse.php` classes. This is where
you will be integrating your application's API with each of the provider's you have
generated. We will cover more about this here.

### Testing
Just like the contracts, each time your run the `orchestrate:service` command you should
expect to see a `FakeServiceRequest.php` & `FakeServiceResponse.php` class pair. These will
serve as your testing gateway where your should mock the response of your applications
requests to the service. More on this here.


## Customization
Just like with Laravel, you are free to organize your application however you like. You may
achieve this by simply updating your service specific config file with the new location of
your service provider gateway implementation. In this example you would need to update the
value of the `checkout.providers.adyen.gateway` key with your desired location.

```php
return [

    'providers' => [

        'adyen' => [
            'gateway' => \App\Services\Checkout\Adyen\AdyenCheckoutRequest::class,
        ],

    ],

];
```
