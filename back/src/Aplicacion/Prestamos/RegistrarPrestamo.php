<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos;

use App\Aplicacion\Prestamos\Dto\PrestamoSalida;
use App\Aplicacion\Prestamos\Dto\RegistrarPrestamoEntrada;
use App\Aplicacion\Prestamos\Dto\VerificarMultasPendientesEntrada;
use App\Aplicacion\Puertos\Reloj;
use App\Aplicacion\Puertos\Transaccion;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Prestamos\Prestamo;
use App\Dominio\Repositorios\RepositorioConfiguracion;
use App\Dominio\Repositorios\RepositorioEjemplares;
use App\Dominio\Repositorios\RepositorioLectores;
use App\Dominio\Repositorios\RepositorioPrestamos;

/** CU-07 Registrar préstamo (RN-01, RN-05). */
final class RegistrarPrestamo
{
    public function __construct(
        private RepositorioLectores $lectores,
        private RepositorioEjemplares $ejemplares,
        private RepositorioPrestamos $prestamos,
        private RepositorioConfiguracion $configuracion,
        private VerificarMultasPendientes $verificarMultas,
        private Reloj $reloj,
        private Transaccion $transaccion,
    ) {}

    public function ejecutar(RegistrarPrestamoEntrada $e): PrestamoSalida
    {
        $lector = $this->lectores->buscarPorIdentificacion($e->identificacionLector)
            ?? throw new RecursoNoEncontradoException("No existe el lector {$e->identificacionLector}.");
        if ($this->verificarMultas->ejecutar(new VerificarMultasPendientesEntrada($e->identificacionLector))) {
            throw new ReglaNegocioException('RN-05', 'El lector tiene multas pendientes y no puede recibir préstamos.');
        }
        $ejemplar = $this->ejemplares->buscarPorCodigo($e->codigoEjemplar)
            ?? throw new RecursoNoEncontradoException("No existe el ejemplar {$e->codigoEjemplar}.");

        $hoy = $this->reloj->hoy();
        $prestamo = new Prestamo(null, $ejemplar, $lector, $hoy, $this->configuracion->vigente());

        $this->transaccion->ejecutar(function () use ($prestamo, $ejemplar, $e) {
            $this->prestamos->guardar($prestamo, $e->usuarioId);
            $this->ejemplares->guardar($ejemplar);
        });

        return PrestamoSalida::desde($prestamo, $hoy);
    }
}
