<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presupuestos extends Model
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

    protected $table = "presupuestos";
    protected $fillable = [
        'tenant_id',
        'numPresupuesto',
        'fechaInicio',
        'fechaFin',
        'estado',
        'facturado',
        'total_sin_iva',
        'descuento',
        'iva',
        'irpf',
        'total',
        'total_iva',
        'total_irpf',
        'observaciones',
        'condiciones',
        'anexo',
        'presupuesto_anexo',
        'cliente_id',
        'contacto_id',
        'cliente_nombre',
        'cliente_dni',
        'cliente_email',
        'cliente_telefono',
        'cliente_cp',
        'cliente_localidad',
        'cliente_provincia',
        'cliente_direccion',
        'metodo_pago',
        'num_cuenta',
        'servicios',
        'fotos',
        'hash',
        'pdf'
    ];
    protected $appends = ['estado_text', 'metodo_text', 'all_productos'];

    /**
     * Accessor for 'estado_text' attribute.
     * Translates the 'estado' integer into human-readable text.
     */
    public function getEstadoTextAttribute()
    {
        return match ($this->estado) {
            0 => 'Pendiente',
            1 => 'Rechazado',
            2 => 'Aceptado',
            3 => 'Facturado Parcialmente',
            4 => 'Facturado',
            default => 'Desconocido',
        };
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

    public function cuenta()
    {
        return $this->belongsTo(Metodos_Pago::class, 'num_cuenta', 'id');
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

    public function setFotosAttribute($value)
    {
        $this->attributes['fotos'] = json_encode($value ?? '{}');
    }

    public function getFotosAttribute($value)
    {
        $decodedValue = json_decode($value, true);
        // Check if json_decode failed or returned null
        if (json_last_error() !== JSON_ERROR_NONE || $decodedValue === null) {
            // Return default value as an array
            return [];
        }
        return $decodedValue;
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class);
    }

    public function contacto()
    {
        return $this->belongsTo(Contacto::class);
    }
    public function anexo()
    {
        return $this->belongsTo(Presupuestos::class, 'presupuesto_anexo');
    }

    public function facturas()
    {
        return $this->hasMany(Facturas::class, 'presupuesto_id');
    }
    public function tokens()
    {
        return $this->hasMany(Token::class);
    }

    public function facturasTotal()
    {

        return $this->facturas()
            ->where('estado', 2)
            ->sum('total');
    }
}
