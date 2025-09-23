<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PresupuestosExport implements FromCollection, WithHeadings
{
    protected $presupuestos;

    public function __construct($presupuestos)
    {
        $this->presupuestos = $presupuestos;
    }

    public function collection()
    {
        return $this->presupuestos->map(function ($presupuesto) {
            return [
                'fechaInicio' => $presupuesto->fechaInicio,
                'numpresupuesto' => $presupuesto->numPresupuesto,
                'cliente' => $presupuesto->cliente?->nombre ?? $presupuesto->contacto?->nombre,
                'dni' => $presupuesto->cliente?->dni ?? $presupuesto->contacto?->dni,
                'total_sin_iva' => floatval($presupuesto->total_sin_iva),
                'total_iva' => floatval($presupuesto->total_iva),
                'total' => floatval($presupuesto->total),
                'estado' => $presupuesto->estado_text
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha de Inicio',
            'Número de Presupuesto',
            'Cliente',
            'DNI',
            'Total sin IVA',
            'Total IVA',
            'Total',
            'Estado'
        ];
    }
}
