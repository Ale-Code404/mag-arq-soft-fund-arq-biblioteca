<?php

namespace App\Infraestructura\Http;

use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Infraestructura\Http\Controladores\Controlador;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Traduce las excepciones a respuestas JSON en español (9.3, RNF-07). */
final class ManejadorErrores
{
    public static function registrar(Exceptions $exceptions): void
    {
        $exceptions->render(fn (ReglaNegocioException $e) => self::json(422, [
            'regla' => $e->regla(),
            'mensaje' => $e->getMessage(),
        ]));

        $exceptions->render(fn (RecursoNoEncontradoException $e) => self::json(404, ['mensaje' => $e->getMessage()]));

        $exceptions->render(fn (ValidationException $e) => self::json(422, [
            'mensaje' => 'Los datos enviados no son válidos.',
            'errores' => $e->errors(),
        ]));

        $exceptions->render(fn (AuthenticationException $e) => self::json(401, ['mensaje' => 'No autenticado.']));

        // Respaldo de la base de datos para las validaciones de unicidad (carreras entre solicitudes)
        $exceptions->render(fn (UniqueConstraintViolationException $e) => self::json(422, [
            'regla' => 'DUPLICADO',
            'mensaje' => 'El registro ya existe.',
        ]));

        $exceptions->render(fn (NotFoundHttpException $e) => self::json(404, ['mensaje' => 'Recurso no encontrado.']));

        $exceptions->render(fn (HttpException $e) => match ($e->getStatusCode()) {
            403 => self::json(403, ['mensaje' => 'No autorizado.']),
            429 => self::json(429, ['mensaje' => 'Demasiados intentos. Intente de nuevo más tarde.'], $e->getHeaders()),
            default => null,
        });
    }

    private static function json(int $estado, array $cuerpo, array $encabezados = []): JsonResponse
    {
        return response()->json($cuerpo, $estado, $encabezados, Controlador::OPCIONES_JSON);
    }
}
