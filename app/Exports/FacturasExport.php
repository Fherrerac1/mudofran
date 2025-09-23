<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FacturasExport implements FromCollection, WithHeadings
{
    protected $facturas;

    public function __construct($facturas)
    {
        $this->facturas = $facturas;
    }

    public function collection()
    {
        return $this->facturas->map(function ($factura) {
            return [
                'fechaInicio' => $factura->fechaInicio,
                'numFactura' => $factura->numFactura,
                'cliente' => $factura->cliente->nombre,
                'dni' => $factura->cliente->dni,
                'total_sin_iva' => floatval($factura->total_sin_iva),
                'total_iva' => floatval($factura->total_iva),
                'retencion' => floatval($factura->Retention()),
                'total' => floatval($factura->total),
                'estado' => $factura->estado_text
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha de Inicio',
            'Número de Factura',
            'Cliente',
            'DNI',
            'Total sin IVA',
            'Total IVA',
            'Retención',
            'Total con Retención',
            'Estado'
        ];
    }
}
