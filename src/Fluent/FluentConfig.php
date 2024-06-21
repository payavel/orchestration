<?php

namespace Payavel\Orchestration\Fluent;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Fluent;

class FluentConfig extends Fluent
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
        return data_get($this->attributes, $key, fn () => config('orchestration.'.$key, $default));
    }

    /**
     * Set an attribute to the fluent instance using "dot" notation.
     *
     * @param  string  $key
     * @param  mixed $default
     * @return void
     */
    public function set($key, $value)
    {
        data_set($this->attributes, $key, $value);
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

        return new static(array_merge(['id' => $id], is_array($config) ? $config : Config::get($config)));
    }
}
