<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class TenantScope
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            session(['tenant_id' => Auth::user()->tenant_id]);
        }

        return $next($request);
    }
}

