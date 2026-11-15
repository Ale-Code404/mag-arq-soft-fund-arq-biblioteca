<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

use App\Aplicacion\Compartido\Salida;

final readonly class HistorialSalida implements Salida
{
    /** @param PrestamoSalida[] $prestamos */
    public function __construct(
        public LectorSalida $lector,
        public array $prestamos,
        public int $totalMultasPendientes,
    ) {}

    public function aArreglo(): array
    {
        return [
            'lector' => $this->lector->aArreglo(),
            'prestamos' => array_map(fn (PrestamoSalida $p) => $p->aArreglo(), $this->prestamos),
            'totalMultasPendientes' => $this->totalMultasPendientes,
        ];
    }
}
