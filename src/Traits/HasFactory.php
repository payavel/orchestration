<?php

namespace Payavel\Serviceable\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory as EloquentHasFactory;
use Illuminate\Support\Str;

trait HasFactory
{
    use EloquentHasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        if (! class_exists($factory = Factory::resolveFactoryName(get_called_class()))) {
            $factory =
                'Payavel\\Serviceable\\Database\\Factories\\' .
                Str::afterLast(get_class(), '\\') .
                'Factory';
        }

        return $factory::new();
    }
}
