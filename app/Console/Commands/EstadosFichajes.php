<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TimeRecord;
use Carbon\Carbon;

class EstadosFichajes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fichajes:estados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los estados de los fichajes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Actualizamos el estado de los fichajes que se les olvido cerrar
        $fichajes = TimeRecord::where('estado', 1)
            ->whereNull('time_out')
            ->get();

        foreach ($fichajes as $fichaje) {
            $fichaje->estado = 0;
            $fichaje->time_out = Carbon::now(); // o la hora que corresponda
            $fichaje->save();
        }

        $this->info('Fichajes actualizados correctamente.');

        return 0;
    }
}
