<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifactuRecord extends Model
{
    use HasFactory;
    protected $table = 'verifactu_records';

    protected $fillable = [
        'verifactu_api_user_id',
        'numFactura',
        'fechaInicio',
        'tax_id',
        'description',
        'hash',
        'total',
    ];

    public function apiUser()
    {
        return $this->belongsTo(VerifactuApiUser::class, 'verifactu_api_user_id');
    }
}
