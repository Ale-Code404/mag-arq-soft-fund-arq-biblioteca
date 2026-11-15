<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Inventario\Dto\RegistrarEjemplarEntrada;
use App\Aplicacion\Inventario\Dto\UbicarEjemplarEntrada;
use App\Aplicacion\Inventario\RegistrarEjemplar;
use App\Aplicacion\Inventario\UbicarEjemplar;
use App\Infraestructura\Http\Solicitudes\RegistrarEjemplarSolicitud;
use App\Infraestructura\Http\Solicitudes\UbicarEjemplarSolicitud;
use Illuminate\Http\JsonResponse;

class EjemplarController extends Controlador
{
    public function store(string $isbn, RegistrarEjemplarSolicitud $request, RegistrarEjemplar $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new RegistrarEjemplarEntrada(
            $isbn,
            $request->input('codigo'),
            $request->input('estanteria'),
            $request->integer('nivel'),
            $this->usuarioId($request),
        )), 201);
    }

    public function ubicar(string $codigo, UbicarEjemplarSolicitud $request, UbicarEjemplar $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new UbicarEjemplarEntrada(
            $codigo,
            $request->input('estanteria'),
            $request->integer('nivel'),
            $this->usuarioId($request),
        )));
    }
}
