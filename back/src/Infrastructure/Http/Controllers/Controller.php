<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Shared\Output;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    /** RNF-05: centimeters keep their decimal (180.0) and text is sent unescaped. */
    public const JSON_OPTIONS = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE;

    protected function respond(Output $output, int $status = 200): JsonResponse
    {
        return response()->json($output->toArray(), $status, [], self::JSON_OPTIONS);
    }

    /** RNF-06: use cases receive who performs the action. */
    protected function userId(Request $request): int
    {
        return $request->user()->id;
    }
}
