<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookModel extends Model
{
    protected $table = 'books';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['year' => 'integer', 'height_cm' => 'float', 'width_cm' => 'float', 'thickness_cm' => 'float'];
    }

    public function copies(): HasMany
    {
        return $this->hasMany(CopyModel::class, 'book_id');
    }
}
