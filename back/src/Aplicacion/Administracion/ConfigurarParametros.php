<?php

declare(strict_types=1);

namespace App\Aplicacion\Administracion;

use App\Aplicacion\Administracion\Dto\ConfiguracionSalida;
use App\Aplicacion\Administracion\Dto\ConfigurarParametrosEntrada;
use App\Aplicacion\Puertos\CacheCapacidad;
use App\Dominio\Configuracion\ConfiguracionBiblioteca;
use App\Dominio\Repositorios\RepositorioConfiguracion;

/** CU-13 Configurar parámetros: guarda una nueva configuración vigente. */
final class ConfigurarParametros
{
    public function __construct(
        private RepositorioConfiguracion $configuracion,
        private CacheCapacidad $cache,
    ) {}

    public function ejecutar(ConfigurarParametrosEntrada $e): ConfiguracionSalida
    {
        $nueva = new ConfiguracionBiblioteca(
            $e->diasPrestamo, $e->diasRenovacion, $e->maxRenovaciones,
            $e->valorMultaDiaria, $e->holguraCm, $e->grosorPorDefectoCm,
        );
        $this->configuracion->guardar($nueva, $e->usuarioId);
        $this->cache->invalidar();   // la holgura y el grosor por defecto cambian las métricas

        return ConfiguracionSalida::desde($nueva);
    }
}
