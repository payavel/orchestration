<?php

namespace Payavel\Orchestration\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait ServesConfig
{
    /**
     * Fetch the config from the corresponding service config, if not found, fall back to the orchestration config.
     *
     * @param string|int $service
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function config($service, $key, $default = null)
    {
        $config = Config::get('orchestration.services.' . $service . '.config', Str::slug($service));

        return Config::get(
            $config .  '.' . $key,
            Config::get(
                'orchestration.' . $key,
                $default
            )
        );
    }
}
