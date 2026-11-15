<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario;

use App\Aplicacion\Inventario\Dto\EjemplarSalida;
use App\Aplicacion\Inventario\Dto\UbicarEjemplarEntrada;
use App\Aplicacion\Puertos\CacheCapacidad;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Repositorios\RepositorioBiblioteca;
use App\Dominio\Repositorios\RepositorioEjemplares;

/** CU-04 Ubicar (o reubicar) un ejemplar en un nivel con RN-06 y RN-07. */
final class UbicarEjemplar
{
    public function __construct(
        private RepositorioEjemplares $ejemplares,
        private RepositorioBiblioteca $biblioteca,
        private CacheCapacidad $cache,
    ) {}

    public function ejecutar(UbicarEjemplarEntrada $e): EjemplarSalida
    {
        $ejemplar = $this->ejemplares->buscarPorCodigo($e->codigo)
            ?? throw new RecursoNoEncontradoException("No existe el ejemplar {$e->codigo}.");

        $this->biblioteca->cargar()->ubicar($ejemplar, $e->estanteria, $e->nivel);
        $this->biblioteca->guardarUbicacion($e->codigo, $e->estanteria, $e->nivel);
        $this->cache->invalidar();

        return EjemplarSalida::desde($ejemplar, $e->estanteria, $e->nivel);
    }
}
