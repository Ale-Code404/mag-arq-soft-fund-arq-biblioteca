<?php

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Services\RedisPermissionCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** DA-12: authorization is resolved before the use case, with cached permissions (RNF-03). */
class CheckPermission
{
    public function __construct(private RedisPermissionCache $permissions) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        abort_unless($user && in_array($permission, $this->permissions->get($user), true), 403, 'No autorizado.');

        return $next($request);
    }
}
