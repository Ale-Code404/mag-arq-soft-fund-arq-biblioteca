<?php

namespace Database\Seeders;

use App\Dominio\Seguridad\Permiso;
use App\Infraestructura\Persistencia\Modelos\RolModelo;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public const ROLES = [
        'Administrador' => [Permiso::USUARIO_GESTIONAR, Permiso::CONFIGURACION_MODIFICAR],
        'Bibliotecario' => [
            Permiso::ESTANTERIA_GESTIONAR, Permiso::LIBRO_REGISTRAR, Permiso::EJEMPLAR_GESTIONAR,
            Permiso::LECTOR_REGISTRAR, Permiso::PRESTAMO_GESTIONAR, Permiso::PRESTAMO_CONSULTAR,
            Permiso::MULTA_REGISTRAR_PAGO, Permiso::CAPACIDAD_CONSULTAR,
        ],
        'Director' => [Permiso::CAPACIDAD_CONSULTAR, Permiso::PRESTAMO_CONSULTAR],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $nombre => $permisos) {
            RolModelo::firstOrCreate(['nombre' => $nombre])
                ->sincronizarPermisos(array_map(fn (Permiso $p) => $p->value, $permisos));
        }
    }
}
