<?php

namespace App\Infraestructura\Servicios;

use App\Aplicacion\Puertos\CachePermisos;
use App\Infraestructura\Persistencia\Modelos\UsuarioModelo;
use Illuminate\Support\Facades\Cache;

class CachePermisosRedis implements CachePermisos
{
    public const TTL_SEGUNDOS = 600;

    /** @return string[] */
    public function obtener(UsuarioModelo $usuario): array
    {
        return Cache::remember(self::clave($usuario->id), self::TTL_SEGUNDOS, fn () => $usuario->permisos());
    }

    public function invalidar(int $usuarioId): void
    {
        Cache::forget(self::clave($usuarioId));
    }

    private static function clave(int $usuarioId): string
    {
        return "permisos:{$usuarioId}";
    }
}
