<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\Loans\Loan;
use App\Domain\People\Reader;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Repositories\ReaderRepository;
use App\Infrastructure\Persistence\Models\CopyModel;
use App\Infrastructure\Persistence\Models\LoanModel;
use App\Infrastructure\Persistence\Models\PersonModel;
use Illuminate\Database\UniqueConstraintViolationException;

class EloquentLoanRepository implements LoanRepository
{
    public function __construct(private ReaderRepository $readers) {}

    public function findActiveByCopy(string $copyCode): ?Loan
    {
        $model = LoanModel::with(['copy.book', 'reader', 'fine'])
            ->whereNull('returned_on')
            ->whereHas('copy', fn ($q) => $q->where('code', $copyCode))
            ->first();

        return $model ? $this->toDomain($model, $this->readers->findByIdentification($model->reader->identification)) : null;
    }

    public function save(Loan $loan, int $userId): void
    {
        if ($loan->id() !== null) {
            LoanModel::whereKey($loan->id())->update($this->toModel($loan));

            return;
        }

        try {
            $model = LoanModel::create($this->toModel($loan) + [
                'copy_id' => CopyModel::where('code', $loan->copy()->code())->value('id'),
                'reader_id' => PersonModel::where('type', PersonModel::READER)
                    ->where('identification', $loan->reader()->identification())->value('id'),
                'loaned_on' => $loan->loanedOn()->format('Y-m-d'),
                'registered_by' => $userId,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Partial unique index loan_active_unique: another request lent the copy first
            throw new BusinessRuleException('R-04', 'El ejemplar ya tiene un préstamo activo.');
        }
        $loan->assignId($model->id);
    }

    public function historyOfReader(string $identification): array
    {
        $reader = $this->readers->findByIdentification($identification);
        if ($reader === null) {
            return [];
        }

        return LoanModel::with(['copy.book', 'fine'])
            ->whereHas('reader', fn ($q) => $q->where('type', PersonModel::READER)->where('identification', $identification))
            ->orderByDesc('loaned_on')
            ->orderByDesc('id')
            ->get()
            ->map(fn (LoanModel $m) => $this->toDomain($m, $reader))
            ->all();
    }

    private function toDomain(LoanModel $m, Reader $reader): Loan
    {
        return Loan::reconstitute(
            $m->id,
            DomainMapper::copy($m->copy),
            $reader,
            $m->loaned_on,
            $m->due_on,
            $m->renewals,
            $m->returned_on,
            $m->fine ? DomainMapper::fine($m->fine) : null,
        );
    }

    private function toModel(Loan $p): array
    {
        return [
            'due_on' => $p->dueOn()->format('Y-m-d'),
            'returned_on' => $p->returnedOn()?->format('Y-m-d'),
            'renewals' => $p->renewals(),
        ];
    }
}
