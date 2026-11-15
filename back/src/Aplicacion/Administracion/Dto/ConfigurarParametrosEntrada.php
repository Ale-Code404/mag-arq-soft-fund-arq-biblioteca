<?php

declare(strict_types=1);

namespace App\Aplicacion\Administracion\Dto;

final readonly class ConfigurarParametrosEntrada
{
    public function __construct(
        public int $diasPrestamo,
        public int $diasRenovacion,
        public int $maxRenovaciones,
        public int $valorMultaDiaria,
        public float $holguraCm,
        public float $grosorPorDefectoCm,
        public int $usuarioId,
    ) {}
}
