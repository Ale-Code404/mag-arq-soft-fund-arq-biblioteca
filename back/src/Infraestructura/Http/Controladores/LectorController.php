<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Prestamos\ConsultarPrestamosYMultas;
use App\Aplicacion\Prestamos\Dto\ConsultarPrestamosYMultasEntrada;
use App\Aplicacion\Prestamos\Dto\RegistrarLectorEntrada;
use App\Aplicacion\Prestamos\RegistrarLector;
use App\Infraestructura\Http\Solicitudes\RegistrarLectorSolicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LectorController extends Controlador
{
    public function store(RegistrarLectorSolicitud $request, RegistrarLector $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new RegistrarLectorEntrada(
            $request->input('identificacion'),
            $request->input('nombre'),
            $request->input('direccion'),
            $this->usuarioId($request),
        )), 201);
    }

    public function historial(string $identificacion, Request $request, ConsultarPrestamosYMultas $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new ConsultarPrestamosYMultasEntrada($identificacion, $this->usuarioId($request))));
    }
}
