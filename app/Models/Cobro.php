<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobro extends Model
{
    use HasFactory;

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
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'observaciones',
        'total',
        'factura_id',
        'fecha_cobro',
    ];

    /**
     * Get the factura that owns the cobro.
     */
    public function factura()
    {
        return $this->belongsTo(Facturas::class);
    }
}
