<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    use HasFactory;

    protected $table = 'datos_perfil';

    protected $fillable = [
        'usuario',
        'nombre',
        'apellidos',
        'email',
        'direccion',
        'ciudad',
        'pais',
        'cp',
        'descripcion'
    ];
}
