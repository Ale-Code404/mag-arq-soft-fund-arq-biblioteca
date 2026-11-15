<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

final readonly class RegistrarLectorEntrada
{
    public function __construct(
        public string $identificacion,
        public string $nombre,
        public ?string $direccion,
        public int $usuarioId,
    ) {}
}
