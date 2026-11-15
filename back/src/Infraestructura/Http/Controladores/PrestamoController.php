<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Prestamos\Dto\RegistrarDevolucionEntrada;
use App\Aplicacion\Prestamos\Dto\RegistrarPrestamoEntrada;
use App\Aplicacion\Prestamos\Dto\RenovarPrestamoEntrada;
use App\Aplicacion\Prestamos\RegistrarDevolucion;
use App\Aplicacion\Prestamos\RegistrarPrestamo;
use App\Aplicacion\Prestamos\RenovarPrestamo;
use App\Infraestructura\Http\Solicitudes\RegistrarDevolucionSolicitud;
use App\Infraestructura\Http\Solicitudes\RegistrarPrestamoSolicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrestamoController extends Controlador
{
    public function store(RegistrarPrestamoSolicitud $request, RegistrarPrestamo $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new RegistrarPrestamoEntrada(
            $request->input('identificacionLector'),
            $request->input('codigoEjemplar'),
            $this->usuarioId($request),
        )), 201);
    }

    public function renovar(string $codigoEjemplar, Request $request, RenovarPrestamo $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new RenovarPrestamoEntrada($codigoEjemplar, $this->usuarioId($request))));
    }

    public function devolver(RegistrarDevolucionSolicitud $request, RegistrarDevolucion $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new RegistrarDevolucionEntrada(
            $request->input('codigoEjemplar'),
            $this->usuarioId($request),
        )));
    }
}
