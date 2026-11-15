<?php

namespace App\Infrastructure\Http;

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Infrastructure\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Translates exceptions into JSON responses with Spanish messages (9.3, RNF-07). */
final class ErrorHandler
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(fn (BusinessRuleException $e) => self::json(422, [
            'rule' => $e->rule(),
            'message' => $e->getMessage(),
        ]));

        $exceptions->render(fn (ResourceNotFoundException $e) => self::json(404, ['message' => $e->getMessage()]));

        $exceptions->render(fn (ValidationException $e) => self::json(422, [
            'message' => 'Los datos enviados no son válidos.',
            'errors' => $e->errors(),
        ]));

        $exceptions->render(fn (AuthenticationException $e) => self::json(401, ['message' => 'No autenticado.']));

        // Database fallback for uniqueness checks (races between requests)
        $exceptions->render(fn (UniqueConstraintViolationException $e) => self::json(422, [
            'rule' => 'DUPLICATE',
            'message' => 'El registro ya existe.',
        ]));

        $exceptions->render(fn (NotFoundHttpException $e) => self::json(404, ['message' => 'Recurso no encontrado.']));

        $exceptions->render(fn (HttpException $e) => match ($e->getStatusCode()) {
            403 => self::json(403, ['message' => 'No autorizado.']),
            429 => self::json(429, ['message' => 'Demasiados intentos. Intente de nuevo más tarde.'], $e->getHeaders()),
            default => null,
        });
    }

    private static function json(int $status, array $body, array $headers = []): JsonResponse
    {
        return response()->json($body, $status, $headers, Controller::JSON_OPTIONS);
    }
}
