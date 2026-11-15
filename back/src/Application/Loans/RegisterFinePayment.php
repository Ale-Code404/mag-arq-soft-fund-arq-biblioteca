<?php

declare(strict_types=1);

namespace App\Application\Loans;

use App\Application\Loans\Dto\FineOutput;
use App\Application\Loans\Dto\RegisterFinePaymentInput;
use App\Application\Ports\Clock;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Repositories\FineRepository;

/** CU-10 Register an in-person fine payment. */
final class RegisterFinePayment
{
    public function __construct(
        private FineRepository $fines,
        private Clock $clock,
    ) {}

    public function execute(RegisterFinePaymentInput $e): FineOutput
    {
        $fine = $this->fines->findById($e->fineId)
            ?? throw new ResourceNotFoundException("No existe la multa {$e->fineId}.");

        $fine->markPaid($this->clock->today());
        $this->fines->registerPayment($fine, $e->userId);

        return FineOutput::from($fine);
    }
}
