<?php

declare(strict_types=1);

namespace App\Application\Loans;

use App\Application\Loans\Dto\LoanOutput;
use App\Application\Loans\Dto\RenewLoanInput;
use App\Application\Ports\Clock;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Repositories\SettingsRepository;

/** CU-08 Renew a loan (RN-02, RN-03). The reader is loaded with their pending fines. */
final class RenewLoan
{
    public function __construct(
        private LoanRepository $loans,
        private SettingsRepository $settings,
        private Clock $clock,
    ) {}

    public function execute(RenewLoanInput $e): LoanOutput
    {
        $loan = $this->loans->findActiveByCopy($e->copyCode)
            ?? throw new ResourceNotFoundException("No hay un préstamo activo para el ejemplar {$e->copyCode}.");

        $today = $this->clock->today();
        $loan->renew($today, $this->settings->current());
        $this->loans->save($loan, $e->userId);

        return LoanOutput::from($loan, $today);
    }
}
