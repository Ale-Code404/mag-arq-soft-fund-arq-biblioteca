<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

final readonly class VerificarMultasPendientesEntrada
{
    public function __construct(
        public string $identificacionLector,
    ) {}
}
