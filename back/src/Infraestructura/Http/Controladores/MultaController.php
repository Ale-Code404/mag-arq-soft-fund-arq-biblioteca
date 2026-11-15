<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Prestamos\Dto\RegistrarPagoMultaEntrada;
use App\Aplicacion\Prestamos\RegistrarPagoMulta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MultaController extends Controlador
{
    public function pagar(int $id, Request $request, RegistrarPagoMulta $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new RegistrarPagoMultaEntrada($id, $this->usuarioId($request))));
    }
}
