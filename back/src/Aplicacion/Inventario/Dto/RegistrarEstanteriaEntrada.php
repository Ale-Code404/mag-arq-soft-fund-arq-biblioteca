<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario\Dto;

final readonly class RegistrarEstanteriaEntrada
{
    public function __construct(
        public string $codigo,
        /** @var array<int, array{numero: int, largoCm: float, altoCm: float}> */
        public array $niveles,
        public int $usuarioId,
    ) {}
}
