<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClienteModelo347Export implements FromCollection, WithHeadings, WithMapping
{
    protected $cliente;

    public function __construct($cliente)
    {
        $this->cliente = $cliente;
    }

    /**
     * Return the collection of facturas for the cliente
     */
    public function collection()
    {
        // Make sure facturas is an iterable collection
        return collect($this->cliente->facturas);
    }

    /**
     * Map the data for each factura
     */
    public function map($factura): array
    {
        return [
            $this->cliente->nombre . ' ' . $this->cliente->apellido_1,
            $factura->fechaInicio,
            $factura->numFactura,
            $factura->presupuesto ? $factura->presupuesto->numPresupuesto : 'no asignado',
            number_format($factura->total - $factura->Retention(), 2, ',', '.') . ' €',
        ];
    }

    /**
     * Define the headers for the table
     */
    public function headings(): array
    {
        return [
            'Cliente',
            'Fecha',
            'Nº Factura',
            'Presupuesto',
            'Total',
        ];
    }
}
