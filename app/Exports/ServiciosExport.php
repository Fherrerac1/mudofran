<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiciosExport implements FromCollection, WithHeadings
{
    protected $servicios;

    public function __construct($servicios)
    {
        $this->servicios = $servicios;
    }

    public function collection()
    {
        return $this->servicios->map(function ($servicio) {
            return [
                'id' => $servicio->id,
                'created_at' => $servicio->created_at->format('Y-m-d H:i:s'),
                'nombre' => $servicio->nombre,
                'observaciones' => $servicio->observaciones,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha de Creación',
            'Nombre del Servicio',
            'Observaciones'
        ];
    }
}
