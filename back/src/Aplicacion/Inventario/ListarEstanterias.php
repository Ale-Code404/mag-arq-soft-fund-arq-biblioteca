<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario;

use App\Aplicacion\Compartido\ColeccionSalida;
use App\Aplicacion\Compartido\ConsultaEntrada;
use App\Aplicacion\Inventario\Dto\EstanteriaSalida;
use App\Dominio\Inventario\Estanteria;
use App\Dominio\Repositorios\RepositorioBiblioteca;

/** Consulta de apoyo a CU-01: estanterías con la ocupación de cada nivel. */
final class ListarEstanterias
{
    public function __construct(private RepositorioBiblioteca $biblioteca) {}

    public function ejecutar(ConsultaEntrada $e): ColeccionSalida
    {
        $biblioteca = $this->biblioteca->cargar();
        $holgura = $biblioteca->configuracion()->holguraCm;

        return new ColeccionSalida(array_map(
            fn (Estanteria $est) => EstanteriaSalida::desde($est, $holgura),
            $biblioteca->estanterias(),
        ));
    }
}
