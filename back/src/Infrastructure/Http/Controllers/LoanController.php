<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Loans\Dto\RegisterLoanInput;
use App\Application\Loans\Dto\RegisterReturnInput;
use App\Application\Loans\Dto\RenewLoanInput;
use App\Application\Loans\RegisterLoan;
use App\Application\Loans\RegisterReturn;
use App\Application\Loans\RenewLoan;
use App\Infrastructure\Http\Requests\RegisterLoanRequest;
use App\Infrastructure\Http\Requests\RegisterReturnRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function store(RegisterLoanRequest $request, RegisterLoan $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new RegisterLoanInput(
            $request->input('readerIdentification'),
            $request->input('copyCode'),
            $this->userId($request),
        )), 201);
    }

    public function renew(string $copyCode, Request $request, RenewLoan $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new RenewLoanInput($copyCode, $this->userId($request))));
    }

    public function returnCopy(RegisterReturnRequest $request, RegisterReturn $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new RegisterReturnInput(
            $request->input('copyCode'),
            $this->userId($request),
        )));
    }
}
