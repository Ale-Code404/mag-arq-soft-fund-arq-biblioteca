<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Compartido\Salida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class Controlador
{
    /** RNF-05: los centímetros conservan su decimal (180.0) y los textos se envían sin escapar. */
    public const OPCIONES_JSON = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE;

    protected function responder(Salida $salida, int $estado = 200): JsonResponse
    {
        return response()->json($salida->aArreglo(), $estado, [], self::OPCIONES_JSON);
    }

    /** RNF-06: los casos de uso reciben quién ejecuta la acción. */
    protected function usuarioId(Request $request): int
    {
        return $request->user()->id;
    }
}
