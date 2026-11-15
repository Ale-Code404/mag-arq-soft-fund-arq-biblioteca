<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Penalties\Fine;
use App\Domain\Penalties\FineStatus;
use App\Domain\Repositories\FineRepository;
use App\Infrastructure\Persistence\Models\FineModel;
use App\Infrastructure\Persistence\Models\PersonModel;

class EloquentFineRepository implements FineRepository
{
    public function readerHasPending(string $identification): bool
    {
        return FineModel::query()
            ->where('status', FineStatus::PENDING->value)
            ->whereHas('loan.reader', fn ($q) => $q
                ->where('type', PersonModel::READER)
                ->where('identification', $identification))
            ->exists();
    }

    public function findById(int $id): ?Fine
    {
        $model = FineModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function add(Fine $fine, int $loanId): void
    {
        $model = FineModel::create($this->toModel($fine) + ['loan_id' => $loanId]);
        $fine->assignId($model->id);
    }

    public function registerPayment(Fine $fine, int $userId): void
    {
        FineModel::whereKey($fine->id())->update($this->toModel($fine) + ['payment_registered_by' => $userId]);
    }

    private function toDomain(FineModel $m): Fine
    {
        return DomainMapper::fine($m);
    }

    private function toModel(Fine $m): array
    {
        return [
            'value' => $m->value(),
            'days_late' => $m->daysLate(),
            'rate_applied' => $m->rateApplied(),
            'status' => $m->status()->value,
            'paid_on' => $m->paidOn()?->format('Y-m-d'),
        ];
    }
}
