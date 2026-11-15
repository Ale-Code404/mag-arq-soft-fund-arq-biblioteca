<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Catalog\Book;
use App\Domain\Inventory\Level;
use App\Domain\Inventory\Library;
use App\Domain\Inventory\Shelf;
use App\Domain\Repositories\LibraryRepository;
use App\Domain\Repositories\SettingsRepository;
use App\Infrastructure\Persistence\Models\CopyModel;
use App\Infrastructure\Persistence\Models\LevelModel;
use App\Infrastructure\Persistence\Models\LibraryModel;
use App\Infrastructure\Persistence\Models\ShelfModel;

class EloquentLibraryRepository implements LibraryRepository
{
    public function __construct(private SettingsRepository $settings) {}

    /** RNF-08: one query per relation thanks to with(). */
    public function load(): Library
    {
        $model = LibraryModel::with('shelves.levels.copies.book')->firstOrFail();

        return $this->toDomain($model);
    }

    public function saveShelf(Shelf $shelf): void
    {
        $model = ShelfModel::create([
            'library_id' => LibraryModel::firstOrFail()->id,
            'code' => $shelf->code(),
        ]);
        foreach ($shelf->levels() as $n) {
            $model->levels()->create(['number' => $n->number(), 'length_cm' => $n->lengthCm(), 'height_cm' => $n->heightCm()]);
        }
    }

    public function saveLocation(string $copyCode, string $shelfCode, int $levelNumber): void
    {
        $levelId = LevelModel::query()
            ->join('shelves', 'shelves.id', '=', 'levels.shelf_id')
            ->where('shelves.code', $shelfCode)
            ->where('levels.number', $levelNumber)
            ->value('levels.id');

        CopyModel::where('code', $copyCode)->update(['level_id' => $levelId]);
    }

    private function toDomain(LibraryModel $m): Library
    {
        /** @var array<int, Book> $books the same book instance is shared by its copies */
        $books = [];
        $shelves = [];
        foreach ($m->shelves as $e) {
            $levels = [];
            foreach ($e->levels as $n) {
                $copies = [];
                foreach ($n->copies as $copy) {
                    $books[$copy->book_id] ??= DomainMapper::book($copy->book);
                    $copies[] = DomainMapper::copy($copy, $books[$copy->book_id]);
                }
                $levels[] = new Level($n->number, $n->length_cm, $n->height_cm, $copies);
            }
            $shelves[] = new Shelf($e->code, $levels);
        }

        return new Library($m->name, $this->settings->current(), $shelves);
    }
}
