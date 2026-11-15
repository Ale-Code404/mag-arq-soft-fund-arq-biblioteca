<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos;

use App\Aplicacion\Prestamos\Dto\LectorSalida;
use App\Aplicacion\Prestamos\Dto\RegistrarLectorEntrada;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Personas\Lector;
use App\Dominio\Repositorios\RepositorioLectores;

/** CU-06 Registrar lector. */
final class RegistrarLector
{
    public function __construct(private RepositorioLectores $lectores) {}

    public function ejecutar(RegistrarLectorEntrada $e): LectorSalida
    {
        if ($this->lectores->existePorIdentificacion($e->identificacion)) {
            throw new ReglaNegocioException('RF-06', 'Ya existe un lector con esa identificación.');
        }
        $lector = new Lector($e->identificacion, $e->nombre, $e->direccion);
        $this->lectores->guardar($lector);

        return LectorSalida::desde($lector);
    }
}
