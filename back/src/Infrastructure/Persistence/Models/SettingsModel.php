<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'loan_days' => 'integer',
            'renewal_days' => 'integer',
            'max_renewals' => 'integer',
            'daily_fine_value' => 'integer',
            'clearance_cm' => 'float',
            'default_thickness_cm' => 'float',
            'effective_from' => 'datetime',
        ];
    }
}
