<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Serviceable Defaults
    |--------------------------------------------------------------------------
    |
    | This option determines the default serviceable settings for your application.
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
    | it must extend the \Payavel\Serviceable\ServiceDriver::class.
    |
    */
    'drivers' => [
        'config' => \Payavel\Serviceable\Drivers\ConfigDriver::class,
        'database' => \Payavel\Serviceable\Drivers\DatabaseDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Serviceable Test Mode
    |--------------------------------------------------------------------------
    |
    | When set to true, the provider & merchant will be shared with the respective
    | fake service request, this allows you to mock your responses as you wish.
    | Note that if the service defines the test_mode, it will be prioritized.
    |
    */
    'test_mode' => env('SERVICE_TEST_MODE', false),

];
