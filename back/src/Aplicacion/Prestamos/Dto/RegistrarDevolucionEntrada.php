<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

final readonly class RegistrarDevolucionEntrada
{
    public function __construct(
        public string $codigoEjemplar,
        public int $usuarioId,
    ) {}
}
