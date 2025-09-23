<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifactuApiUser extends Model
{
    protected $table = 'verifactu_api_users';
    protected $fillable = [
        'name',
        'api_key',
        'email',
        'contact_person',
        'active',
    ];

    public function records()
    {
        return $this->hasMany(VerifactuRecord::class, 'verifactu_api_user_id');
    }
}
