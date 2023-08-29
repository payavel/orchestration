<?php

namespace Payavel\Serviceable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Payavel\Serviceable\Contracts\Serviceable;
use Payavel\Serviceable\Traits\HasFactory;

class Service extends Model implements Serviceable
{
    use HasFactory;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * Get the service's id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the service's name.
     *
     * @return string
     */
    public function getName()
    {
        return Str::headline($this->id);
    }
}
