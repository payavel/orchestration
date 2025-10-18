<?php

namespace Payavel\Orchestration\Traits;

use Illuminate\Contracts\Foundation\CachesConfiguration;

trait MergesConfigRecursively
{
    /**
     * Recursively merges config from the given path.
     *
     * @param string $path
     * @param string $key
     * @return void
     */
    protected function recursivelyMergeConfigFrom(string $path, string $key): void
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
