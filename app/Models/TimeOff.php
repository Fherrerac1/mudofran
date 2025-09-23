<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeOff extends Model
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
    protected $table = 'time_off';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'from',
        'to',
        'estado',
        'accepted_by',
    ];

    // Estado constants
    const ESTADO_PENDIENTE = 0;
    const ESTADO_APROBADO = 1;
    const ESTADO_RECHAZADO = 2;
    const ESTADO_CANCELADO = 3;

    // Etiquetas en español
    public static function estadoLabels(): array
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_APROBADO => 'Aprobado',
            self::ESTADO_RECHAZADO => 'Rechazado',
            self::ESTADO_CANCELADO => 'Cancelado',
        ];
    }

    // Accesor para obtener el nombre del estado
    public function getEstadoLabelAttribute()
    {
        return self::estadoLabels()[$this->estado] ?? 'Desconocido';
    }

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    // Scope para filtrar por estado
    public function scopeWithEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
