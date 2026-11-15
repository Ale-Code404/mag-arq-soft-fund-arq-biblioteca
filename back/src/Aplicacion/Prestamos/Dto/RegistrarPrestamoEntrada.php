<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

final readonly class RegistrarPrestamoEntrada
{
    public function __construct(
        public string $identificacionLector,
        public string $codigoEjemplar,
        public int $usuarioId,
    ) {}
}
