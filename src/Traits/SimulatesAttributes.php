<?php

namespace Payavel\Orchestration\Traits;

use Illuminate\Support\Str;

trait SimulatesAttributes
{
    /**
     * The magic attributes array.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * If a get method exists, it gets executed, otherwise returns a value from the $attributes array.
     *
     * @param string $key.
     * @return mixed
     */
    public function __get($key)
    {
        if (! method_exists(self::class, $method = 'get' . Str::studly($key))) {
            return $this->getAttribute($key);
        }

        return $this->$method();
    }

    /**
     * If a set method exists, it gets executed, otherwise sets value in the $attributes array.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value): void
    {
        if (! method_exists(self::class, $method = 'set' . Str::studly($key))) {
            $this->setAttribute($key, $value);

            return;
        }

        $this->$method($value);
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param string $key
     * @return mixed
     */
    protected function getAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Sets a value in the $attributes array.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }
}
