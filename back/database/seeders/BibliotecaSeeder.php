<?php

namespace Database\Seeders;

use App\Infraestructura\Persistencia\Modelos\BibliotecaModelo;
use Illuminate\Database\Seeder;

class BibliotecaSeeder extends Seeder
{
    public function run(): void
    {
        BibliotecaModelo::firstOrCreate(['nombre' => 'Biblioteca Facultad de Educación UAN']);
    }
}
