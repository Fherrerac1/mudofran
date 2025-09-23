<?php
// app/Models/Concerns/Tenantable.php
namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

trait Tenantable
{
    public static function bootTenantable(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if ($model->tenant_id === null) {
                $model->tenant_id = tenant_id();
            }
        });
    }

    public function scopeForTenant(Builder $query, ?int $tenantId = null): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class)
            ->where($this->getTable() . '.tenant_id', $tenantId ?? tenant_id());
    }

    /** Bypass tenancy for admin/system operations */
    public function newQueryWithoutTenancy(): Builder
    {
        return $this->newQuery()->withoutGlobalScope(TenantScope::class);
    }
}
