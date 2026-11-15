<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Penalties\FineStatus;
use App\Domain\People\Reader;
use App\Domain\Repositories\ReaderRepository;
use App\Infrastructure\Persistence\Models\FineModel;
use App\Infrastructure\Persistence\Models\PersonModel;

class EloquentReaderRepository implements ReaderRepository
{
    public function existsByIdentification(string $identification): bool
    {
        return $this->query($identification)->exists();
    }

    public function findByIdentification(string $identification): ?Reader
    {
        $model = $this->query($identification)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Reader $reader): void
    {
        PersonModel::updateOrCreate(
            ['type' => PersonModel::READER, 'identification' => $reader->identification()],
            $this->toModel($reader),
        );
    }

    private function query(string $identification)
    {
        return PersonModel::where('type', PersonModel::READER)->where('identification', $identification);
    }

    private function toDomain(PersonModel $m): Reader
    {
        $pending = FineModel::query()
            ->whereHas('loan', fn ($q) => $q->where('reader_id', $m->id))
            ->where('status', FineStatus::PENDING->value)
            ->get()
            ->map(fn (FineModel $fine) => DomainMapper::fine($fine))
            ->all();

        return new Reader($m->identification, $m->name, $m->address, $pending);
    }

    private function toModel(Reader $l): array
    {
        return ['name' => $l->getName(), 'address' => $l->address()];
    }
}
