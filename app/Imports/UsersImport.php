<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    // estructura del excel
    public function model(array $row)
    {
        return new User([
            'nombre' => $row['nombre' ],
            'primer apellido' => $row['primer apellido' ],
            'segundo apellido' => $row['segundo apellido'],
            'email' => $row['email' ],
            'teléfono móvil' => $row['teléfono móvil' ],
            'dni/cif' => $row['dni/cif'],
            'cuenta bancaria' => $row['cuenta bancaria'],
            'descripción' => $row['descripción'],
        ]);
    }

    // reglas de validación de campos obligatorios
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'teléfono móvil' => 'required|string',
            'dni/cif' => 'required|string',
        ];
    }
}
