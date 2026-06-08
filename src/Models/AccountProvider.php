<?php

namespace Payavel\Orchestration\Models;

use Payavel\Orchestration\Casts\ProviderFormattedConfig;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Payavel\Orchestration\Traits\HasFactory;

class AccountProvider extends Pivot
{
    use HasFactory;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'config' => ProviderFormattedConfig::class,
    ];
}
