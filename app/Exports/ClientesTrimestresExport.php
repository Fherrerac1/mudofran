<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientesTrimestresExport implements FromCollection, WithHeadings, WithMapping
{
    protected $clientes;

    public function __construct($clientes)
    {
        $this->clientes = $clientes;
    }

    /**
     * Return the collection of clients
     */
    public function collection()
    {
        return collect($this->clientes);
    }

    /**
     * Map the data for each client
     */
    public function map($cliente): array
    {
        return [
            $cliente->dni,
            $cliente->nombre,
            number_format($cliente->trimestre1, 2, ',', '.') . ' €',
            number_format($cliente->trimestre2, 2, ',', '.') . ' €',
            number_format($cliente->trimestre3, 2, ',', '.') . ' €',
            number_format($cliente->trimestre4, 2, ',', '.') . ' €',
            number_format($cliente->total, 2, ',', '.') . ' €',
        ];
    }

    /**
     * Define the headers for the table
     */
    public function headings(): array
    {
        return [
            'N.I.F Cliente',
            'Razón Social',
            'Trimestre 1',
            'Trimestre 2',
            'Trimestre 3',
            'Trimestre 4',
            'Total Importe',
        ];
    }
}
