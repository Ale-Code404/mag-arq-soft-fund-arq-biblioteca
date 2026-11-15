<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Compartido\ConsultaEntrada;
use App\Aplicacion\Inventario\Dto\RegistrarEstanteriaEntrada;
use App\Aplicacion\Inventario\ListarEstanterias;
use App\Aplicacion\Inventario\RegistrarEstanteria;
use App\Infraestructura\Http\Solicitudes\RegistrarEstanteriaSolicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstanteriaController extends Controlador
{
    public function index(Request $request, ListarEstanterias $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new ConsultaEntrada($this->usuarioId($request))));
    }

    public function store(RegistrarEstanteriaSolicitud $request, RegistrarEstanteria $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new RegistrarEstanteriaEntrada(
            $request->input('codigo'),
            $request->input('niveles'),
            $this->usuarioId($request),
        )), 201);
    }
}
