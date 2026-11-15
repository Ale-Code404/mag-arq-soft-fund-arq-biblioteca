<?php

declare(strict_types=1);

namespace App\Aplicacion\Puertos;

interface Transaccion
{
    /** Ejecuta el trabajo de forma atómica (RNF-04) y devuelve su resultado. */
    public function ejecutar(callable $trabajo): mixed;
}
