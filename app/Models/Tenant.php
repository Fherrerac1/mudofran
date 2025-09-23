<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'api_token',
    ];

    // Automatically generate an API token if not provided
    protected static function booted()
    {
        static::creating(function ($tenant) {
            if (empty($tenant->api_token)) {
                $tenant->api_token = Str::random(60);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function facturas()
    {
        return $this->hasMany(Facturas::class);
    }

    public function presupuestos()
    {
        return $this->hasMany(Presupuestos::class);
    }

    public function activities()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function configuration()
    {
        return $this->hasOne(Configuration::class);
    }
}
