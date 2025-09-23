<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{

    protected static function booted()
    {
        // 1️⃣ Apply global tenant scope
        static::addGlobalScope(new TenantScope());

        // 2️⃣ Automatically set tenant_id on create
        static::creating(function ($model) {
            if (session()->has('tenant_id')) {
                $model->tenant_id = session('tenant_id');
            }
        });
    }
    protected $table = 'configuration';
    protected $fillable = [
        'tenant_id',
        'footer_text',
        'text_color',
        'style_color',
        'fecha_fin',
        'url_app',
        'serial_num',
        'pfx_certificate',
        'pfx_password',
        'business_name',
        'address',
        'postal_code',
        'phone',
        'tax_id',
        'business_type',
        'color',
        'town',
        'province',
        'email',
        'tecnicos',
        'series',
        'series_types',
        'factura_mode',
        'unique_color',
        'main_logo',
    ];
}
