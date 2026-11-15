<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Inventory\Dto\GetCapacityInput;
use App\Application\Inventory\GetCapacity;
use App\Infrastructure\Http\Requests\GetCapacityRequest;
use Illuminate\Http\JsonResponse;

class CapacityController extends Controller
{
    public function __invoke(GetCapacityRequest $request, GetCapacity $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new GetCapacityInput(
            $request->input('scope'),
            $request->input('value'),
            $this->userId($request),
        )));
    }
}
