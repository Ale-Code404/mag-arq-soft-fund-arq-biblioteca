<?php

declare(strict_types=1);

namespace App\Application\Loans;

use App\Application\Loans\Dto\CheckPendingFinesInput;
use App\Application\Loans\Dto\LoanOutput;
use App\Application\Loans\Dto\RegisterLoanInput;
use App\Application\Ports\Clock;
use App\Application\Ports\Transaction;
use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Loans\Loan;
use App\Domain\Repositories\CopyRepository;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Repositories\ReaderRepository;
use App\Domain\Repositories\SettingsRepository;

/** CU-07 Register a loan (RN-01, RN-05). */
final class RegisterLoan
{
    public function __construct(
        private ReaderRepository $readers,
        private CopyRepository $copies,
        private LoanRepository $loans,
        private SettingsRepository $settings,
        private CheckPendingFines $checkPendingFines,
        private Clock $clock,
        private Transaction $transaction,
    ) {}

    public function execute(RegisterLoanInput $e): LoanOutput
    {
        $reader = $this->readers->findByIdentification($e->readerIdentification)
            ?? throw new ResourceNotFoundException("No existe el lector {$e->readerIdentification}.");
        if ($this->checkPendingFines->execute(new CheckPendingFinesInput($e->readerIdentification))) {
            throw new BusinessRuleException('RN-05', 'El lector tiene multas pendientes y no puede recibir préstamos.');
        }
        $copy = $this->copies->findByCode($e->copyCode)
            ?? throw new ResourceNotFoundException("No existe el ejemplar {$e->copyCode}.");

        $today = $this->clock->today();
        $loan = new Loan(null, $copy, $reader, $today, $this->settings->current());

        $this->transaction->execute(function () use ($loan, $copy, $e) {
            $this->loans->save($loan, $e->userId);
            $this->copies->save($copy);
        });

        return LoanOutput::from($loan, $today);
    }
}
