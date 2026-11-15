<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Administration\Dto\UpdateSettingsInput;
use App\Application\Administration\GetSettings;
use App\Application\Administration\UpdateSettings;
use App\Application\Shared\QueryInput;
use App\Infrastructure\Http\Requests\UpdateSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request, GetSettings $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new QueryInput($this->userId($request))));
    }

    public function update(UpdateSettingsRequest $request, UpdateSettings $useCase): JsonResponse
    {
        return $this->respond($useCase->execute(new UpdateSettingsInput(
            $request->integer('loanDays'),
            $request->integer('renewalDays'),
            $request->integer('maxRenewals'),
            $request->integer('dailyFineValue'),
            $request->float('clearanceCm'),
            $request->float('defaultThicknessCm'),
            $this->userId($request),
        )));
    }
}
