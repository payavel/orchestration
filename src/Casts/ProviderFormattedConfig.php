<?php

namespace Payavel\Orchestration\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Payavel\Orchestration\Models\Provider;

class ProviderFormattedConfig implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (empty($value)) {
            return [];
        }

        $value = json_decode($value, true);

        $providerConfigFormat = $this->getProviderConfigFormat($model);

        if (!is_array($providerConfigFormat)) {
            return [];
        }

        return array_map(function ($providerConfigKey, $providerConfigRules) use ($value) {
            if (!isset($value[$providerConfigKey])) {
                return null;
            }

            if ($providerConfigRules['encrypt'] ?? false) {
                return Crypt::decrypt($value[$providerConfigKey]);
            }

            return $value[$providerConfigKey];
        }, $providerConfigFormat);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (!is_array($value)) {
            return null;
        }

        $providerConfigFormat = $this->getProviderConfigFormat($model);

        if (!is_array($providerConfigFormat)) {
            return null;
        }

        return json_encode(array_map(function ($providerConfigKey, $providerConfigRules) use ($value) {
            if (!isset($value[$providerConfigKey])) {
                return null;
            }

            if ($providerConfigRules['encrypt'] ?? false) {
                return Crypt::encrypt($value[$providerConfigKey]);
            }

            return $value[$providerConfigKey];
        }, $providerConfigFormat));
    }

    private function getProviderConfigFormat(Pivot $model): ?array
    {
        if ($model->pivotParent instanceof Provider) {
            $provider = $model->pivotParent;
        } elseif ($model->pivotRelated instanceof Provider) {
            $provider = $model->pivotRelated;
        } else {
            return null;
        }

        return $provider->config_format;
    }
}
