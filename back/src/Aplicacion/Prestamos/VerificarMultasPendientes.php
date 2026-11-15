<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos;

use App\Aplicacion\Prestamos\Dto\VerificarMultasPendientesEntrada;
use App\Dominio\Repositorios\RepositorioMultas;

/** CU-14 Verificar si el lector tiene multas pendientes (lo usa CU-07). */
final class VerificarMultasPendientes
{
    public function __construct(private RepositorioMultas $multas) {}

    public function ejecutar(VerificarMultasPendientesEntrada $e): bool
    {
        return $this->multas->lectorTienePendientes($e->identificacionLector);
    }
}
