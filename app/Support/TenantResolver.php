<?php
// app/Support/TenantResolver.php
namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class TenantResolver
{
    public function resolve(?string $host = null): ?int
    {
        // 1) If user is authenticated, trust their tenant
        if ($user = Auth::user()) {
            return $user->tenant_id;
        }

        // 2) Else resolve by subdomain: acme.app.test -> acme
        $host ??= request()->getHost(); // e.g. acme.app.test
        $parts = explode('.', $host);
        $subdomain = count($parts) > 2 ? $parts[0] : null;

        if ($subdomain) {
            $tenant = Tenant::where('slug', $subdomain)->first();
            return $tenant?->id;
        }

        return null;
    }
}
