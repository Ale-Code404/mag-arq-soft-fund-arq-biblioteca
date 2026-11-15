<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Inventario\ConsultarCapacidad;
use App\Aplicacion\Inventario\Dto\ConsultarCapacidadEntrada;
use App\Infraestructura\Http\Solicitudes\ConsultarCapacidadSolicitud;
use Illuminate\Http\JsonResponse;

class CapacidadController extends Controlador
{
    public function __invoke(ConsultarCapacidadSolicitud $request, ConsultarCapacidad $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new ConsultarCapacidadEntrada(
            $request->input('alcance'),
            $request->input('valor'),
            $this->usuarioId($request),
        )));
    }
}
