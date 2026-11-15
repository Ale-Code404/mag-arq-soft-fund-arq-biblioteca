<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos;

use App\Aplicacion\Prestamos\Dto\ConsultarPrestamosYMultasEntrada;
use App\Aplicacion\Prestamos\Dto\HistorialSalida;
use App\Aplicacion\Prestamos\Dto\LectorSalida;
use App\Aplicacion\Prestamos\Dto\PrestamoSalida;
use App\Aplicacion\Puertos\Reloj;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Prestamos\Prestamo;
use App\Dominio\Repositorios\RepositorioLectores;
use App\Dominio\Repositorios\RepositorioPrestamos;

/** CU-11 Historial de préstamos y multas de un lector. */
final class ConsultarPrestamosYMultas
{
    public function __construct(
        private RepositorioLectores $lectores,
        private RepositorioPrestamos $prestamos,
        private Reloj $reloj,
    ) {}

    public function ejecutar(ConsultarPrestamosYMultasEntrada $e): HistorialSalida
    {
        $lector = $this->lectores->buscarPorIdentificacion($e->identificacionLector)
            ?? throw new RecursoNoEncontradoException("No existe el lector {$e->identificacionLector}.");
        $prestamos = $this->prestamos->historialDeLector($e->identificacionLector);
        $hoy = $this->reloj->hoy();

        $pendiente = array_sum(array_map(
            fn (Prestamo $p) => $p->multa()?->estaPendiente() ? $p->multa()->valor() : 0,
            $prestamos,
        ));

        return new HistorialSalida(
            LectorSalida::desde($lector),
            array_map(fn (Prestamo $p) => PrestamoSalida::desde($p, $hoy), $prestamos),
            $pendiente,
        );
    }
}
