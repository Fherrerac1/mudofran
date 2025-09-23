<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
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
    protected $table = "clientes";

    protected $appends = ['nombre_completo'];

    protected $fillable = [
        'nombre',
        'apellido_1',
        'apellido_2',
        'email',
        'telefono_mobile',
        'telefono_fijo',
        'dni',
        'direccion',
        'category',
        'localidad',
        'trabajador_id',
        'provincia',
        'descripcion',
        'num_cuenta',
        'pais',
        'cp',
        'share_data',
        'api_key',
        'dir3',
    ];
    public function trabajador()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalFacturasAttribute()
    {
        return $this->facturas->sum(function ($factura) {
            return $factura->total;
        });
    }
    // Add this method to your Clientes model
    public function facturasThisYear($year)
    {
        $currentYear = $year;

        return $this->facturas()
            ->whereYear('fechaInicio', $currentYear)
            ->get();
    }
    public function facturasThisYearTotal($year)
    {
        $currentYear = $year;

        return $this->facturas()
            ->whereYear('fechaInicio', $currentYear)
            ->get() // Retrieve the filtered records
            ->sum(function ($factura) {
                return $factura->total;
            });
    }

    /**
     * Obtiene el nombre completo del cliente, compuesto por el nombre, primer apellido y segundo apellido.
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return collect([$this->nombre, $this->apellido_1, $this->apellido_2])
            ->filter()
            ->implode(' ');
    }

    // Define a relationship with the Facturas model
    public function facturas()
    {
        return $this->hasMany(Facturas::class, 'cliente_id', 'id');
    }
}
