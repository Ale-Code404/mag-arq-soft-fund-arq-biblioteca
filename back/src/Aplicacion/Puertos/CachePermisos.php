<?php

declare(strict_types=1);

namespace App\Aplicacion\Puertos;

interface CachePermisos
{
    /** Descarta los permisos guardados del usuario después de cambiar sus roles o su estado (RNF-03). */
    public function invalidar(int $usuarioId): void;
}
