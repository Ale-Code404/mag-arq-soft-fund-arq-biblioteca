<?php

use App\Infraestructura\Http\ManejadorErrores;
use App\Infraestructura\Http\Middleware\VerificarPermiso;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['permiso' => VerificarPermiso::class]);
        // API sin sesión: sin token se responde 401, nunca se redirige a una página de login
        $middleware->redirectGuestsTo(fn (Request $request) => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        ManejadorErrores::registrar($exceptions);
    })->create();

// Las capas (Dominio, Aplicacion, Infraestructura) viven en src/ con el espacio de nombres App\
$app->useAppPath($app->basePath('src'));

return $app;
