<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\LibraryModel;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        LibraryModel::firstOrCreate(['name' => 'Biblioteca Facultad de Educación UAN']);
    }
}
