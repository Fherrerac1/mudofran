<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Productos extends Model
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
    protected $table = "productos";

    protected $fillable = [
        'tenant_id',
        'nombre',
        'precio',
        'medida',
        'observaciones',
        'servicio_id',
        'documents',
    ];

    public function setDocumentsAttribute($value)
    {
        // Ensure the value is always set as JSON, if null set as empty object
        $this->attributes['documents'] = json_encode($value ?? []);
    }

    public function getDocumentsAttribute($value)
    {
        $decodedValue = json_decode($value, true);
        // Check if json_decode failed or returned null
        if (json_last_error() !== JSON_ERROR_NONE || $decodedValue === null) {
            return []; // Return an empty array if decoding fails
        }
        return $decodedValue;
    }

    public function servicio()
    {
        return $this->belongsTo(Servicios::class, 'servicio_id');
    }
}
