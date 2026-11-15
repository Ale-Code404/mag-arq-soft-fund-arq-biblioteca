<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Catalog\Copy;
use App\Domain\Repositories\CopyRepository;
use App\Infrastructure\Persistence\Models\BookModel;
use App\Infrastructure\Persistence\Models\CopyModel;

class EloquentCopyRepository implements CopyRepository
{
    public function existsByCode(string $code): bool
    {
        return CopyModel::where('code', $code)->exists();
    }

    public function findByCode(string $code): ?Copy
    {
        $model = CopyModel::with('book')->where('code', $code)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Copy $copy): void
    {
        CopyModel::updateOrCreate(['code' => $copy->code()], $this->toModel($copy));
    }

    public function locationOf(string $code): ?array
    {
        $row = CopyModel::query()
            ->join('levels', 'levels.id', '=', 'copies.level_id')
            ->join('shelves', 'shelves.id', '=', 'levels.shelf_id')
            ->where('copies.code', $code)
            ->first(['estanterias.codigo as estanteria', 'niveles.numero as nivel']);

        return $row ? ['shelf' => $row->shelf, 'level' => (int) $row->level] : null;
    }

    private function toDomain(CopyModel $m): Copy
    {
        return DomainMapper::copy($m);
    }

    private function toModel(Copy $e): array
    {
        return [
            'book_id' => BookModel::where('isbn', $e->book()->isbn())->value('id'),
            'status' => $e->status()->value,
        ];
    }
}
