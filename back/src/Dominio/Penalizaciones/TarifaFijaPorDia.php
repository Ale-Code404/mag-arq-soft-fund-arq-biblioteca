<?php

declare(strict_types=1);

namespace App\Dominio\Penalizaciones;

use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Prestamos\Prestamo;
use DateTimeImmutable;

/** RN-04: valor fijo por cada día de retraso. */
final class TarifaFijaPorDia implements PoliticaMulta
{
    public function __construct(private int $valorDia)
    {
        if ($valorDia <= 0) {
            throw new ReglaNegocioException('RN-04', 'El valor diario de la multa debe ser positivo.');
        }
    }

    public function calcular(Prestamo $prestamo, DateTimeImmutable $fecha): int
    {
        return $prestamo->diasRetraso($fecha) * $this->valorDia;
    }
}
