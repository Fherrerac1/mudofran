<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'presupuesto_id',
        'expires_at',
    ];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuestos::class);
    }
}

