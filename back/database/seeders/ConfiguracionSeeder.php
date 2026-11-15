<?php

namespace Database\Seeders;

use App\Infraestructura\Persistencia\Modelos\BibliotecaModelo;
use App\Infraestructura\Persistencia\Modelos\ConfiguracionModelo;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    /** La multa de 1000 COP y el grosor de 2,5 cm son supuestos (P-02); se cambian con CU-13. */
    public function run(): void
    {
        if (ConfiguracionModelo::exists()) {
            return;
        }
        ConfiguracionModelo::create([
            'biblioteca_id' => BibliotecaModelo::firstOrFail()->id,
            'dias_prestamo' => 15,
            'dias_renovacion' => 15,
            'max_renovaciones' => 1,
            'valor_multa_diaria' => 1000,
            'holgura_cm' => 0.4,
            'grosor_por_defecto_cm' => 2.5,
            'vigente_desde' => now(),
        ]);
    }
}
