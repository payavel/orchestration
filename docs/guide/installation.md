# Installation

There are two ways to get started using orchestration in your Laravel application.
But before we dive into them, we must first require the package using Composer.

```bash
composer require payavel/orchestration
```
Then you may either choose to configure the package [via command](#via-command)
or take the high road and [do it yourself](#manual-configuration).

## Via Command

To quickly get started you can run the following artisan command and follow the steps.
It can't get any easier than that!

```bash
php artisan orchestrate:service
```

This is what the command looks like, it may differ for you based on your responses,
but overall, it is pretty straight forward.

```bash
 What service would you like to add?:
 > Payment

 How would you like to identify the Payment service? [payment]:
 > 

 What payment provider would you like to add?:
 > Adyen

 How would you like to identify the Adyen payment provider? [adyen]:
 > 

 Would you like to add another payment provider? (yes/no) [no]:
 > yes

 What payment provider would you like to add?:
 > Stripe

 How would you like to identify the Stripe payment provider? [stripe]:
 > 

 Would you like to add another payment provider? (yes/no) [no]:
 > 

 Which provider will be used as default?:
  [0] adyen
  [1] stripe
 > 0

 What payment merchant would you like to add?:
 > Payavel

 How would you like to identify the Payavel payment merchant? [payavel]:
 > 

 Which providers will the Payavel merchant be integrating? (default first):
  [0] adyen
  [1] stripe
 > 0,1

 Would you like to add another payment merchant? (yes/no) [no]:
 > 

The payment config has been successfully generated.
Fake payment gateway generated successfully!
Adyen payment gateway generated successfully!
Stripe payment gateway generated successfully!
```

The command will take care of generating all the relevant config files, the service's contracts
& each of the specified provider gateways along with a fake gateway to compliment the service.

::: info :memo: Note
It's also worth mentioning that the first time you run the `orchestrate:service` command, a
"config/orchestration.php" file will be generated. Unfortunately, for consecutive service
installations, you will be forced to register those services within that config file. 
:::

## Manual Configuration

Work in progress...
