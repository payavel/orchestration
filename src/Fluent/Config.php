<?php

namespace Payavel\Orchestration\Fluent;

use Illuminate\Support\Fluent;
use Payavel\Orchestration\Contracts\Serviceable;

class Config extends Fluent implements Serviceable
{
    /**
     * Get the entity id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->attributes['id'];
    }

    /**
     * Get the entity name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->attributes['name'] ?? $this->attributes['id'];
    }

    /**
     * Get an attribute from the fluent instance using "dot" notation.
     *
     * @param  string  $key
     * @param  string|int|mixed|null $default
     * @return string|int|mixed|null
     */
    public function get($key, $default = null)
    {
        return parent::get($key, fn () => config('orchestration.'.$key, $default));
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
}
