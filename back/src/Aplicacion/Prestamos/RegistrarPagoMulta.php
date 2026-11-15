<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos;

use App\Aplicacion\Prestamos\Dto\MultaSalida;
use App\Aplicacion\Prestamos\Dto\RegistrarPagoMultaEntrada;
use App\Aplicacion\Puertos\Reloj;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Repositorios\RepositorioMultas;

/** CU-10 Registrar el pago presencial de una multa. */
final class RegistrarPagoMulta
{
    public function __construct(
        private RepositorioMultas $multas,
        private Reloj $reloj,
    ) {}

    public function ejecutar(RegistrarPagoMultaEntrada $e): MultaSalida
    {
        $multa = $this->multas->buscarPorId($e->multaId)
            ?? throw new RecursoNoEncontradoException("No existe la multa {$e->multaId}.");

        $multa->marcarPagada($this->reloj->hoy());
        $this->multas->registrarPago($multa, $e->usuarioId);

        return MultaSalida::desde($multa);
    }
}
