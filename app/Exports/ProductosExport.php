<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductosExport implements FromCollection, WithHeadings
{
    protected $productos;

    public function __construct($productos)
    {
        $this->productos = $productos;
    }

    public function collection()
    {
        return $this->productos->map(function ($producto) {
            return [
                'created_at' => $producto->created_at ? $producto->created_at->format('d-m-Y') : '',
                'nombre' => $producto->nombre ?? '',
                'servicio' => $producto->servicio->nombre ?? 'Sin servicio',
                'observaciones' => $producto->observaciones ?? '',
                'precio' => number_format($producto->precio ?? 0, 2, ',', '.') . ' €',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Nombre del Producto',
            'Servicio Asociado',
            'Observaciones',
            'Precio',
        ];
    }
}
