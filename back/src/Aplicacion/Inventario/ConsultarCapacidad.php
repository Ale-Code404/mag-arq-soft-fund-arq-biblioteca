<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario;

use App\Aplicacion\Inventario\Dto\CapacidadSalida;
use App\Aplicacion\Inventario\Dto\ConsultarCapacidadEntrada;
use App\Aplicacion\Puertos\CacheCapacidad;
use App\Dominio\Catalogo\Tematica;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Repositorios\RepositorioBiblioteca;

/**
 * CU-05 Consultar capacidad total, por estantería o por temática (fórmulas 6.4).
 * Por temática, el espacio instalado, usado y disponible es el de toda la biblioteca
 * (las estanterías se comparten) y el grosor promedio es el de la temática.
 */
final class ConsultarCapacidad
{
    public const ALCANCES = ['total', 'estanteria', 'tematica'];

    public function __construct(
        private RepositorioBiblioteca $biblioteca,
        private CacheCapacidad $cache,
    ) {}

    public function ejecutar(ConsultarCapacidadEntrada $e): CapacidadSalida
    {
        $valor = $e->alcance === 'total' ? null : $e->valor;
        if ($e->alcance !== 'total' && ($valor === null || $valor === '')) {
            throw new ReglaNegocioException('RF-05', "El alcance {$e->alcance} requiere un valor.");
        }
        $tematica = match ($e->alcance) {
            'total', 'estanteria' => null,
            'tematica' => Tematica::tryFrom($valor) ?? throw new ReglaNegocioException('RF-05', "La temática {$valor} no existe."),
            default => throw new ReglaNegocioException('RF-05', "El alcance {$e->alcance} no es válido."),
        };

        $datos = $this->cache->obtener("capacidad:{$e->alcance}:".($valor ?? 'todo'), function () use ($e, $valor, $tematica) {
            $biblioteca = $this->biblioteca->cargar();
            if ($e->alcance === 'estanteria') {
                $biblioteca = $biblioteca->soloEstanteria($valor);
            }

            return CapacidadSalida::desde($e->alcance, $valor, $biblioteca, $tematica)->aArreglo();
        });

        return CapacidadSalida::desdeArreglo($datos);
    }
}
