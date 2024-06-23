<?php

namespace Payavel\Orchestration\Fluent;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class ServiceConfig extends Fluent
{
    /**
     * Get an attribute from the fluent instance using "dot" notation.
     *
     * @param  string  $key
     * @param  string|int|mixed|null $default
     * @return string|int|mixed|null
     */
    public function get($key, $default = null)
    {
        return data_get($this->attributes, $key, fn () => Config::get('orchestration.'.$key, $default));
    }

    /**
     * Set an attribute to the fluent instance using "dot" notation.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        data_set($this->attributes, $key, $value);

        $this->setConfig($key, $value);
    }

    /**
     * Update the service config with he provided key/value pair.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setConfig($key, $value)
    {
        $config = Config::get('orchestration.services.'.$this->id, Str::slug($this->id));

        Config::set(
            (is_array($config) ? 'orchestration.services.'.$this->id : $config).'.'.$key,
            $value
        );
    }

    /**
     * Get all of the orchestration service configs.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        return (new Collection(Config::get('orchestration.services', [])))->map(
            fn ($value, $key) => new static(array_merge(['id' => $key], is_array($value) ? $value : Config::get($value)))
        )->values();
    }

    /**
     * Get the service's config by it's id.
     *
     * @param string $id
     * @return static
     */
    public static function find($id)
    {
        if (is_null($config = Config::get("orchestration.services.{$id}"))) {
            return null;
        }

        return new static(array_merge(['id' => $id], is_array($config) ? $config : Config::get($config, [])));
    }
}
