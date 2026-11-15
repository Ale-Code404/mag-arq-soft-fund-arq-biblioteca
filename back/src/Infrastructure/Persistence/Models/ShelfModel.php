<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShelfModel extends Model
{
    protected $table = 'shelves';

    protected $guarded = [];

    public function levels(): HasMany
    {
        return $this->hasMany(LevelModel::class, 'shelf_id')->orderBy('number');
    }
}
