<?php

declare(strict_types=1);

namespace App\Aplicacion\Administracion\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Configuracion\ConfiguracionBiblioteca;

final readonly class ConfiguracionSalida implements Salida
{
    public function __construct(
        public int $diasPrestamo,
        public int $diasRenovacion,
        public int $maxRenovaciones,
        public int $valorMultaDiaria,
        public float $holguraCm,
        public float $grosorPorDefectoCm,
    ) {}

    public static function desde(ConfiguracionBiblioteca $c): self
    {
        return new self(...get_object_vars($c));
    }

    public function aArreglo(): array
    {
        return get_object_vars($this);
    }
}
