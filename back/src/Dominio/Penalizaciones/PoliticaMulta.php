<?php

declare(strict_types=1);

namespace App\Dominio\Penalizaciones;

use App\Dominio\Prestamos\Prestamo;
use DateTimeImmutable;

/** Estrategia de cálculo de la multa. Prestamo::devolver() la usa sin conocer la implementación. */
interface PoliticaMulta
{
    public function calcular(Prestamo $prestamo, DateTimeImmutable $fecha): int;
}
