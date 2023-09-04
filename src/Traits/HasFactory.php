<?php

namespace Payavel\Serviceable\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory as HasEloquentFactory;
use Illuminate\Support\Str;

trait HasFactory
{
    use HasEloquentFactory;

    /**
     * Custom factory namespace fallback.
     *
     * @var string
     */
    protected static $factoryNamespace = 'Payavel\\Serviceable\\Database\\Factories';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        if (! class_exists($factory = Factory::resolveFactoryName(get_called_class()))) {
            $factory =
                static::$factoryNamespace .
                '\\' .
                Str::afterLast(get_class(), '\\') .
                'Factory';
        }

        return $factory::new();
    }
}
