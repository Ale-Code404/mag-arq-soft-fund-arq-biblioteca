<?php

namespace App\Infraestructura\Http\Controladores;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class SaludController
{
    public function __invoke(): JsonResponse
    {
        $db = $this->verificar(fn () => DB::select('select 1'));
        $redis = $this->verificar(fn () => Redis::connection()->ping());
        $ok = $db === 'ok' && $redis === 'ok';

        return response()->json(['estado' => $ok ? 'ok' : 'degradado', 'db' => $db, 'redis' => $redis], $ok ? 200 : 503);
    }

    private function verificar(callable $prueba): string
    {
        try {
            $prueba();

            return 'ok';
        } catch (Throwable) {
            return 'error';
        }
    }
}
