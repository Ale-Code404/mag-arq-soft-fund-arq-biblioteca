<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario\Dto;

final readonly class RegistrarLibroEntrada
{
    public function __construct(
        public string $isbn,
        public string $titulo,
        public string $autor,
        public string $editorial,
        public int $anio,
        public string $tematica,
        public float $altoCm,
        public float $anchoCm,
        public float $grosorCm,
        public int $usuarioId,
    ) {}
}
