<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Orchestration Defaults
    |--------------------------------------------------------------------------
    |
    | This option determines the default orchestration settings for your application.
    | It is recommended to use the config driver over the database driver unless
    | you plan to automate the onboarding of new providers for your services.
    |
    */
    'defaults' => [
        'driver' => 'config',
    ],

    /*
    |--------------------------------------------------------------------------
    | Orchestration Drivers
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
    | fake service response, this allows you to mock your responses as you wish.
    | Note that if the service defines the test_mode, it will be prioritized.
    |
    */
    'test_mode' => env('ORCHESTRATION_TEST_MODE', false),

];
