<?php

namespace Payavel\Orchestration\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Serviceable;

class ServiceConfig
{
    /**
     * Fetch the config from the corresponding service, if not found, fall back to the orchestration config.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable|string|int $service
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($service, $key, $default = null)
    {
        if ($service instanceof Serviceable) {
            $service = $service->getId();
        }

        $config = Config::get('orchestration.services.'.$service, Str::slug($service));

        if (is_array($config)) {
            return Config::get('orchestration.services.'.$service.'.'.$key, $default);
        }

        return Config::get(
            $config.'.'.$key,
            Config::get(
                'orchestration.'.$key,
                $default
            )
        );
    }

    /**
     * Set config for the corresponding service.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable|string|int $service
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($service, string $key, $value)
    {
        if ($service instanceof Serviceable) {
            $service = $service->getId();
        }

        $config = Config::get('orchestration.services.'.$service, Str::slug($service));

        Config::set(
            (is_array($config) ? 'orchestration.services.'.$service : $config).'.'.$key,
            $value
        );
    }
}
