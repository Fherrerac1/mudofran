<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Helpers\BackupHelper;

class FichajesExport implements FromCollection, WithHeadings, WithEvents
{
    /**
     * Colección de registros horarios a exportar
     */
    protected $horarios;

    /**
     * Acumuladores de tiempos totales en segundos
     */
    protected $totales = [
        'total' => 0,
        'pausa' => 0,
        'comida' => 0,
        'trabajado' => 0,
        'efectivo' => 0,
    ];

    public function __construct($horarios)
    {
        $this->horarios = $horarios;
    }

    /**
     * Collection de horarios con sus respectivos datos y totales de pausa, comida, trabajado y efectivo.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->horarios->map(function ($horario) {
            // Convertir y acumular tiempo total (HH:MM)
            if (!empty($horario->total)) {
                [$h, $m] = explode(':', $horario->total);
                $this->totales['total'] += ((int) $h * 3600) + ((int) $m * 60);
            }

            // Convertir y acumular pausa (HH:MM:SS)
            if (!empty($horario->pause_total_formatted) && $horario->pause_total_formatted !== '00:00:00') {
                [$h, $m, $s] = explode(':', $horario->pause_total_formatted);
                $this->totales['pausa'] += ((int) $h * 3600) + ((int) $m * 60) + (int) $s;
            }

            // Convertir y acumular comida (HH:MM:SS)
            if (!empty($horario->meal_total_formatted) && $horario->meal_total_formatted !== '00:00:00') {
                [$h, $m, $s] = explode(':', $horario->meal_total_formatted);
                $this->totales['comida'] += ((int) $h * 3600) + ((int) $m * 60) + (int) $s;
            }

            // Convertir y acumular trabajado (HH:MM)
            if (!empty($horario->worked_time_formatted)) {
                [$h, $m] = explode(':', $horario->worked_time_formatted);
                $this->totales['trabajado'] += ((int) $h * 3600) + ((int) $m * 60);
            }

            // Convertir y acumular efectivo (HH:MM)
            if (!empty($horario->effective_time_formatted)) {
                [$h, $m] = explode(':', $horario->effective_time_formatted);
                $this->totales['efectivo'] += ((int) $h * 3600) + ((int) $m * 60);
            }

            // Devolver los datos de la fila del Excel
            return [
                'Nombre'          => $horario->user['name'] ?? '-',
                'Puesto'          => $horario->user['position'] ?? '-',
                'Email'           => $horario->user['email'] ?? '-',
                'Fecha'           => Carbon::parse($horario->created_at)->format('d-m-Y'),
                'Inicio'          => $horario->time_in,
                'Final'           => $horario->time_out ?? 'En curso',
                'Comida'          => ($horario->meal_total_formatted && $horario->meal_total_formatted !== '00:00:00') ? $horario->meal_total_formatted : '-',
                'Pausa'           => ($horario->pause_total_formatted && $horario->pause_total_formatted !== '00:00:00') ? $horario->pause_total_formatted : '-',
                'TiempoTrabajado' => $horario->worked_time_formatted ?? '-',
                'TiempoTotal'  => $horario->total ?? 'En curso',
                'Estado'          => $horario->estado_text,
                'Observaciones'   => BackupHelper::parseObservaciones($horario->observaciones),

            ];
            // Agregar la fila final con totales formateados
        })->push([
            'Nombre'          => '',
            'Puesto'          => '',
            'Email'           => '',
            'Fecha'           => '',
            'Inicio'          => '',
            'Final'           => '',
            'Comida'          => $this->formatSegundos($this->totales['comida']),
            'Pausa'           => $this->formatSegundos($this->totales['pausa']),
            'TiempoTrabajado' => $this->formatSegundos($this->totales['trabajado']),
            'TiempoTotal'  => $this->formatSegundos($this->totales['total']),
            'Estado'          => '',
            'Observaciones'   => '',

        ]);
    }

    /**
     * Define las cabeceras del Excel
     */
    public function headings(): array
    {
        return [
            'Nombre',
            'Puesto',
            'Email',
            'Fecha',
            'Inicio',
            'Final',
            'Comida',
            'Pausa',
            'Tiempo Trabajado',
            'Tiempo Total',
            'Estado',
            'Observaciones', 

        ];
    }

    /**
     * Formatea un total de segundos a una cadena en formato HH:MM.
     *
     * @param int $totalSegundos N mero de segundos a formatear.
     * @return string Cadena en formato HH:MM con el total de segundos.
     */
    protected function formatSegundos($totalSegundos)
    {
        $horas = floor($totalSegundos / 3600);
        $minutos = floor(($totalSegundos % 3600) / 60);
        return str_pad($horas, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutos, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Registra estilos a aplicar al exportar (como negrita en la fila de totales)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Obtener la última fila que corresponde a los totales
                $lastRow = $this->horarios->count() + 2;

                // Aplicar negrita a las celdas G, H, I y J (Comida, Pausa, Tiempo Efectivo, Tiempo Trabajado)
                $event->sheet->getStyle("G{$lastRow}:J{$lastRow}")
                    ->getFont()->setBold(true);
            }
        ];
    }
}
