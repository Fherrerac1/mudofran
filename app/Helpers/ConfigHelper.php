<?php

namespace App\Helpers;

use App\Models\Configuration;

class ConfigHelper
{
    public static function get($key, $default = null)
    {
        $configuration = Configuration::first();
        return $configuration->$key ?? $default;
    }
}