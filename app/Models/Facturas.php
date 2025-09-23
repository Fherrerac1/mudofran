<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturas extends Model
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
    protected $table = "facturas";

    protected $fillable = [
        'tenant_id',
        'numFactura',
        'fechaInicio',
        'fechaFin',
        'estado',
        'total_sin_iva',
        'descuento',
        'iva',
        'irpf',
        'total',
        'total_iva',
        'total_irpf',
        'concepto',
        'factura_nativa',
        'observaciones',
        'condiciones',
        'presupuesto_id',
        'cliente_id',
        'cliente_nombre',
        'cliente_dni',
        'cliente_email',
        'cliente_telefono',
        'cliente_cp',
        'cliente_localidad',
        'cliente_provincia',
        'cliente_direccion',
        'metodo_pago',
        'correcto',
        'serie',
        'retencion',
        'tiempo',
        'num_cuenta',
        'porcentaje',
        'servicios',
        'hash',
        'qr_code',
        'pdf'
    ];

    protected $appends = ['estado_text', 'metodo_text', 'total_cobrado', 'all_productos'];

    /**
     * Accessor for 'estado_text' attribute.
     * Translates the 'estado' integer into human-readable text.
     */
    public function getEstadoTextAttribute()
    {
        return match ($this->estado) {
            0 => 'Pendiente',
            1 => 'Anulada',
            2 => 'Pagado',
            3 => 'Rectificada',
            4 => 'Aceptado',
            5 => 'Pagado Parcialmente',
            default => 'Desconocido',
        };
    }

    public function getTotalCobradoAttribute()
    {
        return $this->totalCobros();
    }

    public function getMetodoTextAttribute()
    {
        return match ($this->metodo_pago) {
            0 => 'Transferencia Bancaria ' . ($this->cuenta?->metodo ?? 'No especificado'),
            1 => 'Giro Bancario',
            2 => 'Efectivo',
            3 => 'Confirming',
            default => 'Desconocido',
        };
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(Metodos_Pago::class, 'num_cuenta', 'id');
    }
    public function cobros()
    {
        return $this->hasMany(Cobro::class, 'factura_id', 'id');
    }

    public function totalCobros()
    {
        return round($this->cobros()->sum('total'), 2);
    }

    public function nativa()
    {
        return $this->belongsTo(Facturas::class, 'factura_nativa', 'id');
    }

    public function facturas_recurrente()
    {
        return $this->belongsTo(FacturaRecurrente::class, 'id', 'factura_id');
    }

    public function Retention()
    {
        if ($this->retencion == true) {
            // If serie is 1, calculate 5% of total_sin_iva
            return round($this->total_sin_iva * 0.05, 2);
        } elseif ($this->retencion == false) {
            // If serie is 0, retention is 0%
            return 0;
        } else {
            // Handle other cases if needed
            return 0;
        }
    }
    public function Iva()
    {
        return $this->total_sin_iva * ($this->iva / 100);
    }


    public function RetentionPercentage()
    {
        if ($this->retencion == true) {
            // If serie is 1, return 5
            return 5;
        } elseif ($this->retencion == false) {
            // If serie is 0, return 0
            return 0;
        } else {
            // Handle other cases if needed
            return 0;
        }
    }
    public function presupuesto()
    {
        return $this->hasOne(Presupuestos::class, 'id', 'presupuesto_id');
    }

    public function setServiciosAttribute($value)
    {
        $this->attributes['servicios'] = json_encode($value ?? '{}');
    }

    public function getServiciosAttribute($value)
    {
        $decodedValue = json_decode($value, true);
        // Check if json_decode failed or returned null
        if (json_last_error() !== JSON_ERROR_NONE || $decodedValue === null) {
            // Return default value as an array
            return [];
        }
        return $decodedValue;
    }

    // Accessor for all productos
    public function getAllProductosAttribute()
    {
        $productos = [];

        if (!empty($this->servicios)) {
            foreach ($this->servicios as $servicio) {
                if (!empty($servicio['productos'])) {
                    $productos = array_merge($productos, $servicio['productos']);
                }
            }
        }

        return $productos;
    }
}
