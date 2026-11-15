<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoanModel extends Model
{
    protected $table = 'loans';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'loaned_on' => 'immutable_date',
            'due_on' => 'immutable_date',
            'returned_on' => 'immutable_date',
            'renewals' => 'integer',
        ];
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(CopyModel::class, 'copy_id');
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(PersonModel::class, 'reader_id');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(FineModel::class, 'loan_id');
    }
}
