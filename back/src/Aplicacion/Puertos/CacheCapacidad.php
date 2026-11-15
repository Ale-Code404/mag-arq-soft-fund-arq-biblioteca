<?php

declare(strict_types=1);

namespace App\Aplicacion\Puertos;

interface CacheCapacidad
{
    /** Devuelve las métricas guardadas en la clave o las calcula y guarda (RNF-08). */
    public function obtener(string $clave, callable $calcular): array;

    /** Descarta todas las métricas guardadas. */
    public function invalidar(): void;
}
