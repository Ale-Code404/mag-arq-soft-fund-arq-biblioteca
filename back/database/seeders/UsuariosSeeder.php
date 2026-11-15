<?php

namespace Database\Seeders;

use App\Infraestructura\Persistencia\Modelos\PersonaModelo;
use App\Infraestructura\Persistencia\Modelos\RolModelo;
use App\Infraestructura\Persistencia\Modelos\UsuarioModelo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public const CLAVE = 'Cambiar123*';

    private const USUARIOS = [
        ['login' => 'admin', 'identificacion' => '900000001', 'nombre' => 'Administrador del sistema', 'cargo' => 'Administrador', 'rol' => 'Administrador'],
        ['login' => 'bibliotecario', 'identificacion' => '900000002', 'nombre' => 'Bibliotecario de turno', 'cargo' => 'Bibliotecario', 'rol' => 'Bibliotecario'],
        ['login' => 'director', 'identificacion' => '900000003', 'nombre' => 'Dirección de biblioteca', 'cargo' => 'Director', 'rol' => 'Director'],
    ];

    public function run(): void
    {
        foreach (self::USUARIOS as $u) {
            $persona = PersonaModelo::firstOrCreate(
                ['tipo' => PersonaModelo::FUNCIONARIO, 'identificacion' => $u['identificacion']],
                ['nombre' => $u['nombre'], 'cargo' => $u['cargo']],
            );
            $usuario = UsuarioModelo::firstOrCreate(
                ['login' => $u['login']],
                ['persona_id' => $persona->id, 'password' => Hash::make(self::CLAVE), 'activo' => true],
            );
            $usuario->roles()->syncWithoutDetaching([RolModelo::where('nombre', $u['rol'])->value('id')]);
        }
    }
}
