<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Inventory\Dto\PlaceCopyInput;
use App\Application\Inventory\Dto\RegisterCopyInput;
use App\Application\Inventory\PlaceCopy;
use App\Application\Inventory\RegisterCopy;
use App\Infrastructure\Http\Requests\PlaceCopyRequest;
use App\Infrastructure\Http\Requests\RegisterCopyRequest;
use Illuminate\Http\JsonResponse;

class CopyController extends Controller
{
    public function store(string $isbn, RegisterCopyRequest $request, RegisterCopy $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new RegisterCopyInput(
            $isbn,
            $request->input('code'),
            $request->input('shelf'),
            $request->integer('level'),
            $this->userId($request),
        )), 201);
    }

    public function place(string $code, PlaceCopyRequest $request, PlaceCopy $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new PlaceCopyInput(
            $code,
            $request->input('shelf'),
            $request->integer('level'),
            $this->userId($request),
        )));
    }
}
