<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos;

use App\Aplicacion\Prestamos\Dto\DevolucionSalida;
use App\Aplicacion\Prestamos\Dto\MultaSalida;
use App\Aplicacion\Prestamos\Dto\PrestamoSalida;
use App\Aplicacion\Prestamos\Dto\RegistrarDevolucionEntrada;
use App\Aplicacion\Puertos\CacheCapacidad;
use App\Aplicacion\Puertos\Reloj;
use App\Aplicacion\Puertos\Transaccion;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Penalizaciones\TarifaFijaPorDia;
use App\Dominio\Repositorios\RepositorioConfiguracion;
use App\Dominio\Repositorios\RepositorioEjemplares;
use App\Dominio\Repositorios\RepositorioMultas;
use App\Dominio\Repositorios\RepositorioPrestamos;

/** CU-09 Registrar devolución; incluye CU-15 (multa por retraso, RN-04). */
final class RegistrarDevolucion
{
    public function __construct(
        private RepositorioPrestamos $prestamos,
        private RepositorioEjemplares $ejemplares,
        private RepositorioMultas $multas,
        private RepositorioConfiguracion $configuracion,
        private Reloj $reloj,
        private Transaccion $transaccion,
        private CacheCapacidad $cache,
    ) {}

    public function ejecutar(RegistrarDevolucionEntrada $e): DevolucionSalida
    {
        $prestamo = $this->prestamos->buscarActivoPorEjemplar($e->codigoEjemplar)
            ?? throw new RecursoNoEncontradoException("No hay un préstamo activo para el ejemplar {$e->codigoEjemplar}.");
        $hoy = $this->reloj->hoy();
        $politica = new TarifaFijaPorDia($this->configuracion->vigente()->valorMultaDiaria);

        $multa = $this->transaccion->ejecutar(function () use ($prestamo, $hoy, $politica, $e) {
            $multa = $prestamo->devolver($hoy, $politica);
            $this->prestamos->guardar($prestamo, $e->usuarioId);
            $this->ejemplares->guardar($prestamo->ejemplar());
            if ($multa !== null) {
                $this->multas->agregar($multa, $prestamo->id());
            }

            return $multa;
        });
        $this->cache->invalidar();

        return new DevolucionSalida(PrestamoSalida::desde($prestamo, $hoy), $multa ? MultaSalida::desde($multa) : null);
    }
}
