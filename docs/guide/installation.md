# Installation

## Prerequisites
- [PHP](https://www.php.net) version 7.4 or higher.
- [Laravel](https://laravel.com) version 8 or higher.
- Any service you would like to orchestrate.

### Composer

Orchestration may be installed into a new or existing Laravel application, in both cases, we must require the package via Composer.

```bash
composer require payavel/orchestration
```

## Setup Wizard

To quickly get started you can run the following artisan command and follow the steps.
It can't get any easier than that!

```bash
php artisan orchestrate:service
```

This is what the command looks like, it may differ for you based on your responses,
but overall, it is pretty straight forward.

```bash
 What service would you like to add?:
 > Checkout

 How would you like to identify the Checkout service? [checkout]:
 > checkout

 Which driver will handle the Checkout service? [config]:
  [0] config
  [1] database
 > 0

 What checkout provider would you like to add?:
 > Adyen

 How would you like to identify the Adyen checkout provider? [adyen]:
 > adyen

 Would you like to add another checkout provider? (yes/no) [no]:
 > yes

 What checkout provider would you like to add?:
 > Stripe

 How would you like to identify the Stripe checkout provider? [stripe]:
 > stripe

 Would you like to add another checkout provider? (yes/no) [no]:
 > no

 Which provider will be used as default?:
  [0] adyen
  [1] stripe
 > 0

 What checkout merchant would you like to add?:
 > Payavel

 How would you like to identify the Payavel checkout merchant? [payavel]:
 > payavel

 Which providers will the Payavel merchant be integrating? (default first):
  [0] adyen
  [1] stripe
 > 0,1

 Would you like to add another checkout merchant? (yes/no) [no]:
 > no

The checkout config has been successfully generated.
Fake checkout gateway generated successfully!
Adyen checkout gateway generated successfully!
Stripe checkout gateway generated successfully!
```

The command will take care of generating all the relevant config files, the service's contracts
& each of the specified provider gateways along with a fake gateway to help with testing.

::: info :memo: Note
The first time you run the `orchestrate:service` command, a "config/orchestration.php" file will
be generated and poulated with the service configurations. For consecutive orchestrations, you
will be forced to configer the services manually.
:::
