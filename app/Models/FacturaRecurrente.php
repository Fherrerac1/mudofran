<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FacturaRecurrente extends Model
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
    protected $table = "facturas_recurrentes";
    protected $fillable = ['factura_id', 'frecuencia', 'fechaInicio', 'fechaFin', 'repeticiones', 'repeticiones_actuales', 'proxima_fecha'];

    public function factura()
    {
        return $this->belongsTo(Facturas::class, 'factura_id');
    }

    /**
     * Calculate the next recurrence date based on the frequency.
     *
     * @return Carbon|null
     */
    public function calcularProximaFecha()
    {
        // Use proxima_fecha as the base if it's set, otherwise fall back to fechaInicio
        $fecha = Carbon::parse($this->proxima_fecha);

        // Calculate the next recurrence date based on the frequency
        $proximaFecha = match ($this->frecuencia) {
            'diaria' => $fecha->addDay(),
            'semanal' => $fecha->addWeek(),
            'mensual' => $fecha->addMonth(),
            'trimestral' => $fecha->addMonths(3),
            'semestral' => $fecha->addMonths(6),
            'anual' => $fecha->addYear(),
            default => null,
        };

        // Set the proxima_fecha property if a valid date is calculated
        if ($proximaFecha) {
            $this->proxima_fecha = $proximaFecha;
            $this->save();  // Optionally save it immediately if needed
        }

        return $proximaFecha;
    }

    /**
     * Lista de frecuencias válidas para facturas recurrentes.
     *
     * @return array<string, string>
     */
    public static function getFrecuencias()
    {
        return [
            'diaria' => 'Diaria',
            'semanal' => 'Semanal',
            'mensual' => 'Mensual',
            'trimestral' => 'Trimestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual',
        ];
    }
}
