<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Inventario\Dto\RegistrarLibroEntrada;
use App\Aplicacion\Inventario\RegistrarLibro;
use App\Infraestructura\Http\Solicitudes\RegistrarLibroSolicitud;
use Illuminate\Http\JsonResponse;

class LibroController extends Controlador
{
    public function store(RegistrarLibroSolicitud $request, RegistrarLibro $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new RegistrarLibroEntrada(
            $request->input('isbn'),
            $request->input('titulo'),
            $request->input('autor'),
            $request->input('editorial'),
            $request->integer('anio'),
            $request->input('tematica'),
            $request->float('altoCm'),
            $request->float('anchoCm'),
            $request->float('grosorCm'),
            $this->usuarioId($request),
        )), 201);
    }
}
