<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicios extends Model
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
    protected $table = "servicios";

    protected $fillable = [
        'tenant_id',
        'nombre',
        'observaciones'
    ];

    public function productos()
    {
        return $this->hasMany(Productos::class, 'servicio_id', 'id');
    }
}
