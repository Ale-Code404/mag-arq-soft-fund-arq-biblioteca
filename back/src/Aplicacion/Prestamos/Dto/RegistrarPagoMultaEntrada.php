<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

final readonly class RegistrarPagoMultaEntrada
{
    public function __construct(
        public int $multaId,
        public int $usuarioId,
    ) {}
}
