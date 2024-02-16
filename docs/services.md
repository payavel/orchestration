# Services
This package is meant to assist you in orchestrating multiple providers & merchants for all your services.

We refer to service as any non-mutable communication made between your application and your preferred service providers on your merchant's behalf. This does not necessarily mean that your application cannot act as one of the providers. As a general rule of thumb, it is recommended to persist data outside the gateway implementation to maintain the extra layer of abstraction this package was meant for.

You should make a structured request and expect to receive a structured response back from your service regardless of the provider.

## Providers
Providers are responsible for resolving your application's needs for any specific service, they should receive a request in your applications language, map it to the providers language, make the request, receive a response & finally map that response to a format that your application expects and understands.

## Merchants
Whenever you make a request to a service, you must surely be doing it on behalf of we call a "merchant", this way you can set up multiple accounts for a single service provider if you ever need too. In most cases you will probably be using a single merchant in your application, if this also applies to you, we have made it very easy for you to not have to worry about the merchant, you just need to make sure it is set as default for your service.
