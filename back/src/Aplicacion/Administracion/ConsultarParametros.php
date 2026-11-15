<?php

declare(strict_types=1);

namespace App\Aplicacion\Administracion;

use App\Aplicacion\Administracion\Dto\ConfiguracionSalida;
use App\Aplicacion\Compartido\ConsultaEntrada;
use App\Dominio\Repositorios\RepositorioConfiguracion;

/** Consulta de apoyo a CU-13: configuración vigente. */
final class ConsultarParametros
{
    public function __construct(private RepositorioConfiguracion $configuracion) {}

    public function ejecutar(ConsultaEntrada $e): ConfiguracionSalida
    {
        return ConfiguracionSalida::desde($this->configuracion->vigente());
    }
}
