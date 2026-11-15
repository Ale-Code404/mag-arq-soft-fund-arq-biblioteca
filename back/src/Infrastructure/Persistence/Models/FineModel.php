<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FineModel extends Model
{
    protected $table = 'fines';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'days_late' => 'integer',
            'rate_applied' => 'integer',
            'paid_on' => 'immutable_date',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoanModel::class, 'loan_id');
    }
}
