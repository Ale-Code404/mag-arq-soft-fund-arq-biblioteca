<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Loans\Dto\GetLoansAndFinesInput;
use App\Application\Loans\Dto\RegisterReaderInput;
use App\Application\Loans\GetLoansAndFines;
use App\Application\Loans\RegisterReader;
use App\Infrastructure\Http\Requests\RegisterReaderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReaderController extends Controller
{
    public function store(RegisterReaderRequest $request, RegisterReader $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new RegisterReaderInput(
            $request->input('identification'),
            $request->input('name'),
            $request->input('address'),
            $this->userId($request),
        )), 201);
    }

    public function history(string $identification, Request $request, GetLoansAndFines $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new GetLoansAndFinesInput($identification, $this->userId($request))));
    }
}
