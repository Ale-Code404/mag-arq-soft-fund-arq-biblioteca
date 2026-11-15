<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario;

use App\Aplicacion\Inventario\Dto\EstanteriaSalida;
use App\Aplicacion\Inventario\Dto\RegistrarEstanteriaEntrada;
use App\Aplicacion\Puertos\CacheCapacidad;
use App\Aplicacion\Puertos\Transaccion;
use App\Dominio\Inventario\Estanteria;
use App\Dominio\Inventario\Nivel;
use App\Dominio\Repositorios\RepositorioBiblioteca;

/** CU-01 Registrar estantería con sus niveles. */
final class RegistrarEstanteria
{
    public function __construct(
        private RepositorioBiblioteca $biblioteca,
        private CacheCapacidad $cache,
        private Transaccion $transaccion,
    ) {}

    public function ejecutar(RegistrarEstanteriaEntrada $e): EstanteriaSalida
    {
        $biblioteca = $this->biblioteca->cargar();
        $estanteria = new Estanteria($e->codigo, array_map(
            fn (array $n) => new Nivel((int) $n['numero'], (float) $n['largoCm'], (float) $n['altoCm']),
            $e->niveles,
        ));
        $biblioteca->agregarEstanteria($estanteria);   // código único

        $this->transaccion->ejecutar(fn () => $this->biblioteca->guardarEstanteria($estanteria));
        $this->cache->invalidar();

        return EstanteriaSalida::desde($estanteria, $biblioteca->configuracion()->holguraCm);
    }
}
