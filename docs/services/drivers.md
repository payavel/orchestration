# Drivers
Drivers give you a highly customizable way for you to orchestrate your service's providers & merchants. You may use use different drivers for each one of your services, depending on the requirements for that service within your application. For example, you may want to onboard merchants dynamically by adding a record to yur application and specifying the credentials to utilize for each compatible provider. In other cases you might not require such flexibility, you may simply register those merchants in your service specific config file.

## Available Out of the Box
To cover both of the scenarios above we offer 2 drivers out of the box.

### The Config Driver
For most applications this would be the go to driver, especially if you are consuming services on your behalf, you will most likely require a single merchant for this use case.

### The Database Driver
This driver is recommended when your application handles requests for an undefined number of merchants that consume your integrated services on their behalf.

::: info :memo: Note
When leveraging the database driver, make sure to publish the base migration by running the `php artisan vendor:publish --tag='payavel-migrations'` and then running the migration via the `php artisan migrate` command.
:::

## Custom Drivers
If none of the available drivers satisfies your application's needs you may also define your own custom driver. You will need to make sure it extends the `Payavel\Orchestration\ServiceDriver::class`, implement it's abstract functions and register it within the `orchestration.drivers` config array.
