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

];
