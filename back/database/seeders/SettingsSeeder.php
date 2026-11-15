<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\LibraryModel;
use App\Infrastructure\Persistence\Models\SettingsModel;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /** The 1000 COP fine and 2.5 cm thickness are assumptions (P-02); they change through CU-13. */
    public function run(): void
    {
        if (SettingsModel::exists()) {
            return;
        }
        SettingsModel::create([
            'library_id' => LibraryModel::firstOrFail()->id,
            'loan_days' => 15,
            'renewal_days' => 15,
            'max_renewals' => 1,
            'daily_fine_value' => 1000,
            'clearance_cm' => 0.4,
            'default_thickness_cm' => 2.5,
            'effective_from' => now(),
        ]);
    }
}
