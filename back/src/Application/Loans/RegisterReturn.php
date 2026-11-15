<?php

declare(strict_types=1);

namespace App\Application\Loans;

use App\Application\Loans\Dto\FineOutput;
use App\Application\Loans\Dto\LoanOutput;
use App\Application\Loans\Dto\RegisterReturnInput;
use App\Application\Loans\Dto\ReturnOutput;
use App\Application\Ports\CapacityCache;
use App\Application\Ports\Clock;
use App\Application\Ports\Transaction;
use App\Domain\Exceptions\ResourceNotFoundException;
use App\Domain\Penalties\FixedDailyRate;
use App\Domain\Repositories\CopyRepository;
use App\Domain\Repositories\FineRepository;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Repositories\SettingsRepository;

/** CU-09 Register a return; includes CU-15 (late fine, RN-04). */
final class RegisterReturn
{
    public function __construct(
        private LoanRepository $loans,
        private CopyRepository $copies,
        private FineRepository $fines,
        private SettingsRepository $settings,
        private Clock $clock,
        private Transaction $transaction,
        private CapacityCache $cache,
    ) {}

    public function execute(RegisterReturnInput $e): ReturnOutput
    {
        $loan = $this->loans->findActiveByCopy($e->copyCode)
            ?? throw new ResourceNotFoundException("No hay un préstamo activo para el ejemplar {$e->copyCode}.");
        $today = $this->clock->today();
        $policy = new FixedDailyRate($this->settings->current()->dailyFineValue);

        $fine = $this->transaction->execute(function () use ($loan, $today, $policy, $e) {
            $fine = $loan->returnCopy($today, $policy);
            $this->loans->save($loan, $e->userId);
            $this->copies->save($loan->copy());
            if ($fine !== null) {
                $this->fines->add($fine, $loan->id());
            }

            return $fine;
        });
        $this->cache->invalidate();

        return new ReturnOutput(LoanOutput::from($loan, $today), $fine ? FineOutput::from($fine) : null);
    }
}
