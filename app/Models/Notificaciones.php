<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\MyNotifications;
use Carbon\Carbon;

class Notificaciones extends Model
{
    use HasFactory;
    protected $table = 'notificaciones';

    protected static function booted()
    {
        // 1️⃣ Apply global tenant scope
        static::addGlobalScope(new TenantScope());

        // 2️⃣ Automatically set tenant_id on create
        static::creating(function ($model) {
            if (session()->has('tenant_id')) {
                $model->tenant_id = session('tenant_id');
            }
        });
    }

    protected $fillable = ['title', 'tipo', 'content', 'user_id', 'tenant_id'];

    protected static function boot()
    {
        parent::boot();
        static::created(function ($notification) {
            event(new MyNotifications($notification));
        });
    }

    public function viewers()
    {
        return $this->hasMany(Viewers::class, 'notification_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
