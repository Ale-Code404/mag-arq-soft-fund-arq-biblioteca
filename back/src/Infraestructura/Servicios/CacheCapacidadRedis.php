<?php

namespace App\Infraestructura\Servicios;

use App\Aplicacion\Puertos\CacheCapacidad;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Las claves llevan una versión (capacidad:v{n}:{alcance}:{valor}); invalidar sube la versión,
 * con lo que todas las métricas anteriores quedan obsoletas sin recorrer claves en Redis.
 */
class CacheCapacidadRedis implements CacheCapacidad
{
    public const TTL_SEGUNDOS = 300;

    private const CLAVE_VERSION = 'capacidad:version';

    public function obtener(string $clave, callable $calcular): array
    {
        $version = (int) Cache::get(self::CLAVE_VERSION, 0);
        $claveVersionada = preg_replace('/^capacidad:/', "capacidad:v{$version}:", $clave);

        return Cache::remember($claveVersionada, self::TTL_SEGUNDOS, fn () => $calcular());
    }

    public function invalidar(): void
    {
        // Después del commit, para que una lectura concurrente no vuelva a guardar datos viejos
        DB::afterCommit(function () {
            Cache::add(self::CLAVE_VERSION, 0);
            Cache::increment(self::CLAVE_VERSION);
        });
    }
}
