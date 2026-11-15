<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Inventory\Dto\RegisterShelfInput;
use App\Application\Inventory\ListShelves;
use App\Application\Inventory\RegisterShelf;
use App\Application\Shared\QueryInput;
use App\Infrastructure\Http\Requests\RegisterShelfRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function index(Request $request, ListShelves $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new QueryInput($this->userId($request))));
    }

    public function store(RegisterShelfRequest $request, RegisterShelf $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new RegisterShelfInput(
            $request->input('code'),
            $request->input('levels'),
            $this->userId($request),
        )), 201);
    }
}
