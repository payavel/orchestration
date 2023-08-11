<?php

namespace Payavel\Serviceable\Traits;

use Illuminate\Support\Facades\Config;

trait ServiceableConfig
{
    /**
     * Fetch the config from the corresponding service config, if not found, fall back to the serviceable config.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        $serviceKey = Config::get('serviceable.services.' . $this->service->getId() . '.config', $this->service->getId()) . '.' . $key;

        return Config::get(
            $serviceKey,
            Config::get(
                'serviceable.' . $key,
                $default
            )
        );
    }
}
