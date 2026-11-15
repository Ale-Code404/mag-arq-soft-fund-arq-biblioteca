<?php

namespace App\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthController
{
    public function __invoke(): JsonResponse
    {
        $db = $this->check(fn () => DB::select('select 1'));
        $redis = $this->check(fn () => Redis::connection()->ping());
        $ok = $db === 'ok' && $redis === 'ok';

        return response()->json(['status' => $ok ? 'ok' : 'degraded', 'db' => $db, 'redis' => $redis], $ok ? 200 : 503);
    }

    private function check(callable $probe): string
    {
        try {
            $probe();

            return 'ok';
        } catch (Throwable) {
            return 'error';
        }
    }
}
