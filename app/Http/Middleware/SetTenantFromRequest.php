<?php
// app/Http/Middleware/SetTenantFromRequest.php
namespace App\Http\Middleware;

use App\Support\TenantResolver;
use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SetTenantFromRequest
{
    public function __construct(private TenantResolver $resolver)
    {
    }

    public function handle($request, Closure $next)
    {
        $tenantId = $this->resolver->resolve();

        // For tenant-scoped routes, fail hard if not found to avoid leakage
        if (!$tenantId) {
            throw new NotFoundHttpException('Tenant not resolved.');
        }

        app()->instance('tenant_id', $tenantId);

        return $next($request);
    }
}
