<?php

declare(strict_types=1);

namespace App\Application\Loans;

use App\Application\Loans\Dto\GetLoansAndFinesInput;
use App\Application\Loans\Dto\HistoryOutput;
use App\Application\Loans\Dto\LoanOutput;
use App\Application\Loans\Dto\ReaderOutput;
use App\Application\Ports\Clock;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Loans\Loan;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Repositories\ReaderRepository;

/** CU-11 History of loans and fines for a reader. */
final class GetLoansAndFines
{
    public function __construct(
        private ReaderRepository $readers,
        private LoanRepository $loans,
        private Clock $clock,
    ) {}

    public function execute(GetLoansAndFinesInput $e): HistoryOutput
    {
        $reader = $this->readers->findByIdentification($e->readerIdentification)
            ?? throw new ResourceNotFoundException("No existe el lector {$e->readerIdentification}.");
        $loans = $this->loans->historyOfReader($e->readerIdentification);
        $today = $this->clock->today();

        $pending = array_sum(array_map(
            fn (Loan $p) => $p->fine()?->isPending() ? $p->fine()->value() : 0,
            $loans,
        ));

        return new HistoryOutput(
            ReaderOutput::from($reader),
            array_map(fn (Loan $p) => LoanOutput::from($p, $today), $loans),
            $pending,
        );
    }
}
