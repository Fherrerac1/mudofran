<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlantillaMaestra;

class PlantillaMaestraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Inserta plantillas maestras de tipo "Factura" en la base de datos,
     * asociadas a distintas series (1, 2, 5, 7, 11) con sus configuraciones específicas.
     *
     * Cada plantilla define:
     *  - Tipo de documento (Factura)
     *  - Letra prefijo (F, P, R)
     *  - Serie
     *  - Formato de año (yy)
     *  - Cantidad de dígitos
     *  - Símbolos separadores
     *  - numeroSerieActivo Indica si el numero de serie esta activo es decir visible para el usuario
     *  - Orden (letra, year, numeroSerieActivo, opcional, cantidad)
     */
    public function run(): void
    {
        $ordenPorDefecto = ['letra', 'year', 'numeroSerieActivo', 'opcional', 'cantidad'];

        $plantillas = [
            [
                'tipo' => 'Factura',
                'letra' => 'F',
                'serie' => 1,                       // Factura Normal
                'year' => 'yy',
                'cantidad' => 6,
                'simbolo_1' => '-',
                'simbolo_2' => '/',
                'opcional' => 'C1',
                'numeroSerieActivo' => true,
                'orden' => $ordenPorDefecto,
            ],
            [
                'tipo' => 'Factura',
                'letra' => 'P',
                'serie' => 11,                       // Factura borrador
                'year' => 'yy',
                'cantidad' => 6,
                'simbolo_1' => '-',
                'simbolo_2' => '/',
                'opcional' => 'C1',
                'numeroSerieActivo' => true,
                'orden' => $ordenPorDefecto,
            ],
            [
                'tipo' => 'Factura',
                'letra' => 'R',
                'serie' => 2,                       // Factura Rectificativa
                'year' => 'yy',
                'cantidad' => 6,
                'simbolo_1' => '-',
                'simbolo_2' => '/',
                'opcional' => 'C1',
                'numeroSerieActivo' => true,
                'orden' => $ordenPorDefecto,
            ],
            [
                'tipo' => 'Factura',
                'letra' => 'F',
                'serie' => 5,                       // Factura Autofacturación
                'year' => 'yy',
                'cantidad' => 6,
                'simbolo_1' => '-',
                'simbolo_2' => '/',
                'opcional' => 'C1',
                'numeroSerieActivo' => true,
                'orden' => $ordenPorDefecto,
            ],
            [
                'tipo' => 'Factura',
                'letra' => 'F',
                'serie' => 11,                       // Borrador Factura
                'year' => 'yy',
                'cantidad' => 6,
                'simbolo_1' => '-',
                'simbolo_2' => '/',
                'opcional' => 'C1',
                'numeroSerieActivo' => true,
                'orden' => $ordenPorDefecto,
            ],
        ];

        foreach ($plantillas as $plantilla) {
            PlantillaMaestra::create($plantilla);
        }
    }
}
