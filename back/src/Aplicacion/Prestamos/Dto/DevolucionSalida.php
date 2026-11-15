<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

use App\Aplicacion\Compartido\Salida;

final readonly class DevolucionSalida implements Salida
{
    public function __construct(
        public PrestamoSalida $prestamo,
        public ?MultaSalida $multa,
    ) {}

    public function aArreglo(): array
    {
        return ['prestamo' => $this->prestamo->aArreglo(), 'multa' => $this->multa?->aArreglo()];
    }
}
