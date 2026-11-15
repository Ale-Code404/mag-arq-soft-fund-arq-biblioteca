<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Loans\Dto\RegisterFinePaymentInput;
use App\Application\Loans\RegisterFinePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function pay(int $id, Request $request, RegisterFinePayment $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new RegisterFinePaymentInput($id, $this->userId($request))));
    }
}
