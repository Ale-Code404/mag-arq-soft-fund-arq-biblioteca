<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Inventory\Dto\RegisterBookInput;
use App\Application\Inventory\RegisterBook;
use App\Infrastructure\Http\Requests\RegisterBookRequest;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function store(RegisterBookRequest $request, RegisterBook $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new RegisterBookInput(
            $request->input('isbn'),
            $request->input('title'),
            $request->input('author'),
            $request->input('publisher'),
            $request->integer('year'),
            $request->input('genre'),
            $request->float('heightCm'),
            $request->float('widthCm'),
            $request->float('thicknessCm'),
            $this->userId($request),
        )), 201);
    }
}
