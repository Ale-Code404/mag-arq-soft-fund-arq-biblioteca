<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario\Dto;

final readonly class ConsultarCapacidadEntrada
{
    public function __construct(
        public string $alcance,
        public ?string $valor,
        public int $usuarioId,
    ) {}
}
