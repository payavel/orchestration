<?php

namespace Payavel\Orchestration\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory as HasEloquentFactory;
use Illuminate\Support\Str;

trait HasFactory
{
    use HasEloquentFactory;

    /**
     * Creates a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory(): Factory
    {
        if (! class_exists($factory = Factory::resolveFactoryName(get_called_class()))) {
            $factory =
                static::getFactoryNamespace() .
                '\\' .
                Str::afterLast(get_class(), '\\') .
                'Factory';
        }

        return $factory::new();
    }

    /**
     * Custom factory namespace fallback.
     *
     * @return string
     */
    protected static function getFactoryNamespace(): string
    {
        return 'Payavel\\Orchestration\\Database\\Factories';
    }
}
