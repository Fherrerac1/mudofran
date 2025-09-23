<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class PlantillaMaestra extends Model
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
    protected $table = 'plantillas_maestras';

    protected $fillable = [
        'tenant_id',
        'letra',
        'serie',
        'year',
        'cantidad',
        'opcional',
        'tipo',
        'simbolo_1',
        'simbolo_2',
        'numeroSerieActivo',
        'orden',
        'updated_by',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'serie' => 'integer',
        'updated_by' => 'integer',
        'orden' => 'array',
    ];

    protected static $labels = [
        'letra' => 'Letra',
        'year' => 'Año',
        'cantidad' => 'Cantidad',
        'opcional' => 'Opcional',
        'tipo' => 'Tipo',
        'serie' => 'Serie',
        'simbolo_1' => 'Símbolo 1',
        'simbolo_2' => 'Símbolo 2',
        'numeroSerieActivo' => 'N° Serie Activo',
        'orden' => 'Orden',
        'updated_by' => 'Actualizado por',
    ];

    /**
     * Accesor que construye el código de vista previa según el orden dinámico.
     */
    public function getCodigoAttribute(): string
    {
        $valores = [
            'letra' => $this->letra,
            'year' => $this->year,
            'serie' => $this->serie,
            'simbolo_1' => $this->simbolo_1,
            'simbolo_2' => $this->simbolo_2,
            'opcional' => $this->opcional,
            'numero' => str_pad('1', $this->cantidad, '0', STR_PAD_LEFT),
        ];

        $orden = $this->orden ?? ['letra', 'year', 'numeroSerieActivo', 'opcional', 'cantidad'];
        $partes = [];

        foreach ($orden as $index => $campo) {
            $isFirst = $index === 0;

            if ($campo === 'letra') {
                $partes[] = $valores['letra'];
            }

            if ($campo === 'year') {
                $partes[] = $isFirst
                    ? $valores['year'] . $valores['simbolo_1']
                    : $valores['simbolo_1'] . $valores['year'];
            }

            if ($campo === 'numeroSerieActivo' && $this->numeroSerieActivo) {
                $partes[] = $isFirst
                    ? $valores['serie'] . $valores['simbolo_1']
                    : $valores['simbolo_1'] . $valores['serie'];
            }

            if ($campo === 'opcional' && $valores['opcional']) {
                $partes[] = $isFirst
                    ? $valores['opcional'] . $valores['simbolo_2']
                    : $valores['simbolo_2'] . $valores['opcional'];
            }

            if ($campo === 'cantidad') {
                $partes[] = $isFirst
                    ? $valores['numero'] . $valores['simbolo_2']
                    : $valores['simbolo_2'] . $valores['numero'];
            }
        }

        return implode('', $partes);
    }

    /**
     * Recupera la lista de campos de la tabla asociada al modelo.
     */
    public function getFields(): array
    {
        return Schema::getColumnListing($this->table);
    }

    /**
     * Relación con el usuario que actualizó el registro.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Devuelve un array con los campos relevantes para mostrar en una tabla.
     */
    public function toDisplayArray(): array
    {
        return [
            'Letra' => $this->letra,
            'Serie' => $this->serie,
            'Año' => $this->year,
            'Cantidad' => $this->cantidad,
            'Símbolo 1' => $this->simbolo_1,
            'Símbolo 2' => $this->simbolo_2,
            'Opcional' => $this->opcional ?: '—',
            'Tipo' => $this->tipo,
            'Orden' => implode(' → ', $this->orden ?? ['letra', 'year', 'numeroSerieActivo', 'opcional', 'cantidad']),
            'Código Vista Previa' => $this->codigo,
            'Actualizado por' => $this->updatedBy?->name ?? 'blanco/vacio',
        ];
    }
}
