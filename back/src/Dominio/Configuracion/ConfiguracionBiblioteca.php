<?php

declare(strict_types=1);

namespace App\Dominio\Configuracion;

use App\Dominio\Excepciones\ReglaNegocioException;

/** Parámetros vigentes de la biblioteca. Objeto de valor inmutable (CU-13). */
final readonly class ConfiguracionBiblioteca
{
    public function __construct(
        public int $diasPrestamo,
        public int $diasRenovacion,
        public int $maxRenovaciones,
        public int $valorMultaDiaria,
        public float $holguraCm,
        public float $grosorPorDefectoCm,
    ) {
        foreach (get_object_vars($this) as $nombre => $valor) {
            if ($valor <= 0) {
                throw new ReglaNegocioException('RF-13', "El parámetro {$nombre} debe ser positivo.");
            }
        }
    }
}
