<?php

namespace App\Infraestructura\Http\Controladores;

use App\Infraestructura\Http\Solicitudes\LoginSolicitud;
use App\Infraestructura\Persistencia\Modelos\UsuarioModelo;
use App\Infraestructura\Servicios\CachePermisosRedis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController
{
    public const INTENTOS_POR_MINUTO = 5;

    public function __construct(private CachePermisosRedis $permisos) {}

    /** RNF-11: máximo 5 intentos por minuto por login e IP. */
    public function login(LoginSolicitud $request): JsonResponse
    {
        $clave = 'login:'.Str::lower($request->string('login')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($clave, self::INTENTOS_POR_MINUTO)) {
            $segundos = RateLimiter::availableIn($clave);

            return response()->json(['mensaje' => "Demasiados intentos. Intente de nuevo en {$segundos} segundos."], 429, ['Retry-After' => $segundos]);
        }

        $usuario = UsuarioModelo::where('login', $request->string('login'))->first();
        if (! $usuario || ! $usuario->activo || ! Hash::check($request->string('clave'), $usuario->password)) {
            RateLimiter::hit($clave, 60);

            return response()->json(['mensaje' => 'Credenciales inválidas.'], 401);
        }
        RateLimiter::clear($clave);

        return response()->json([
            'token' => $usuario->createToken('api')->plainTextToken,
            'usuario' => $this->perfil($usuario),
        ]);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function yo(Request $request): JsonResponse
    {
        return response()->json($this->perfil($request->user()));
    }

    private function perfil(UsuarioModelo $u): array
    {
        $u->loadMissing(['persona', 'roles']);

        return [
            'id' => $u->id,
            'login' => $u->login,
            'nombre' => $u->persona->nombre,
            'roles' => $u->roles->pluck('nombre')->all(),
            'permisos' => $this->permisos->obtener($u),
        ];
    }
}
