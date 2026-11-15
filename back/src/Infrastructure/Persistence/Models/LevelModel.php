<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LevelModel extends Model
{
    protected $table = 'levels';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['number' => 'integer', 'length_cm' => 'float', 'height_cm' => 'float'];
    }

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(ShelfModel::class, 'shelf_id');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(CopyModel::class, 'level_id')->orderBy('id');
    }
}
