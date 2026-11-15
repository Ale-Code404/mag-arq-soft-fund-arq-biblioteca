<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

final readonly class RenovarPrestamoEntrada
{
    public function __construct(
        public string $codigoEjemplar,
        public int $usuarioId,
    ) {}
}
