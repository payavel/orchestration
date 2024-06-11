<?php

namespace Payavel\Orchestration\Traits;

use Illuminate\Contracts\Foundation\CachesConfiguration;

trait MergesConfigRecursively
{
    protected function recursivelyMergeConfigFrom($path, $key)
    {
        if (! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');

            $config->set($key, array_replace_recursive(
                require $path,
                $config->get($key, [])
            ));
        }
    }
}
