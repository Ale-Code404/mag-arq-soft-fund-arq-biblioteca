<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario\Dto;

final readonly class RegistrarEjemplarEntrada
{
    public function __construct(
        public string $isbn,
        public string $codigo,
        public string $estanteria,
        public int $nivel,
        public int $usuarioId,
    ) {}
}
