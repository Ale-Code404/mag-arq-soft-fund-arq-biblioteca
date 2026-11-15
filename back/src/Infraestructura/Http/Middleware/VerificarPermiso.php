<?php

namespace App\Infraestructura\Http\Middleware;

use App\Infraestructura\Servicios\CachePermisosRedis;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** DA-12: la autorización se resuelve antes del caso de uso, con los permisos en caché (RNF-03). */
class VerificarPermiso
{
    public function __construct(private CachePermisosRedis $permisos) {}

    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $usuario = $request->user();
        abort_unless($usuario && in_array($permiso, $this->permisos->obtener($usuario), true), 403, 'No autorizado.');

        return $next($request);
    }
}
