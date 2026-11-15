<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryModel extends Model
{
    protected $table = 'libraries';

    protected $guarded = [];

    public function shelves(): HasMany
    {
        return $this->hasMany(ShelfModel::class, 'library_id')->orderBy('code');
    }
}
