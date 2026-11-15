<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos;

use App\Aplicacion\Prestamos\Dto\PrestamoSalida;
use App\Aplicacion\Prestamos\Dto\RenovarPrestamoEntrada;
use App\Aplicacion\Puertos\Reloj;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Repositorios\RepositorioConfiguracion;
use App\Dominio\Repositorios\RepositorioPrestamos;

/** CU-08 Renovar préstamo (RN-02, RN-03). El lector llega con sus multas pendientes. */
final class RenovarPrestamo
{
    public function __construct(
        private RepositorioPrestamos $prestamos,
        private RepositorioConfiguracion $configuracion,
        private Reloj $reloj,
    ) {}

    public function ejecutar(RenovarPrestamoEntrada $e): PrestamoSalida
    {
        $prestamo = $this->prestamos->buscarActivoPorEjemplar($e->codigoEjemplar)
            ?? throw new RecursoNoEncontradoException("No hay un préstamo activo para el ejemplar {$e->codigoEjemplar}.");

        $hoy = $this->reloj->hoy();
        $prestamo->renovar($hoy, $this->configuracion->vigente());
        $this->prestamos->guardar($prestamo, $e->usuarioId);

        return PrestamoSalida::desde($prestamo, $hoy);
    }
}
