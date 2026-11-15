<?php

namespace App\Infrastructure\Http\Controllers;

use App\Infrastructure\Http\Requests\LoginRequest;
use App\Infrastructure\Persistence\Models\UserModel;
use App\Infrastructure\Services\RedisPermissionCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController
{
    public const ATTEMPTS_PER_MINUTE = 5;

    public function __construct(private RedisPermissionCache $permissions) {}

    /** RNF-11: at most 5 attempts per minute per login and IP. */
    public function login(LoginRequest $request): JsonResponse
    {
        $key = 'login:'.Str::lower($request->string('login')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, self::ATTEMPTS_PER_MINUTE)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json(['message' => "Demasiados intentos. Intente de nuevo en {$seconds} segundos."], 429, ['Retry-After' => $seconds]);
        }

        $user = UserModel::where('login', $request->string('login'))->first();
        if (! $user || ! $user->active || ! Hash::check($request->string('password'), $user->password)) {
            RateLimiter::hit($key, 60);

            return response()->json(['message' => 'Credenciales inválidas.'], 401);
        }
        RateLimiter::clear($key);

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => $this->profile($user),
        ]);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->profile($request->user()));
    }

    private function profile(UserModel $u): array
    {
        $u->loadMissing(['person', 'roles']);

        return [
            'id' => $u->id,
            'login' => $u->login,
            'name' => $u->person->name,
            'roles' => $u->roles->pluck('name')->all(),
            'permissions' => $this->permissions->get($u),
        ];
    }
}
