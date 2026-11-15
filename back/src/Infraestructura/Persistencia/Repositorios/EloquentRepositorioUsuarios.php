<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Personas\Funcionario;
use App\Dominio\Repositorios\RepositorioUsuarios;
use App\Dominio\Seguridad\Permiso;
use App\Dominio\Seguridad\Rol;
use App\Dominio\Seguridad\Usuario;
use App\Infraestructura\Persistencia\Modelos\PersonaModelo;
use App\Infraestructura\Persistencia\Modelos\RolModelo;
use App\Infraestructura\Persistencia\Modelos\UsuarioModelo;

class EloquentRepositorioUsuarios implements RepositorioUsuarios
{
    public function buscarPorId(int $id): ?Usuario
    {
        $modelo = UsuarioModelo::with(['persona', 'roles'])->find($id);

        return $modelo ? $this->aDominio($modelo) : null;
    }

    public function existeLogin(string $login, ?int $exceptoId = null): bool
    {
        return UsuarioModelo::where('login', $login)
            ->when($exceptoId, fn ($q) => $q->whereKeyNot($exceptoId))
            ->exists();
    }

    public function existeFuncionario(string $identificacion): bool
    {
        return PersonaModelo::where('tipo', PersonaModelo::FUNCIONARIO)->where('identificacion', $identificacion)->exists();
    }

    public function rolesPorNombre(array $nombres): array
    {
        return RolModelo::whereIn('nombre', $nombres)->orderBy('nombre')->get()
            ->map(fn (RolModelo $r) => $this->rolADominio($r))
            ->all();
    }

    public function guardar(Usuario $usuario): void
    {
        $persona = PersonaModelo::updateOrCreate(
            ['tipo' => PersonaModelo::FUNCIONARIO, 'identificacion' => $usuario->persona()->identificacion()],
            ['nombre' => $usuario->persona()->getNombre(), 'cargo' => $usuario->persona()->cargo()],
        );
        $datos = $this->aModelo($usuario) + ['persona_id' => $persona->id];
        $modelo = $usuario->id() === null
            ? UsuarioModelo::create($datos)
            : tap(UsuarioModelo::findOrFail($usuario->id()))->update($datos);
        $modelo->roles()->sync(RolModelo::whereIn('nombre', array_map(fn (Rol $r) => $r->nombre(), $usuario->roles()))->pluck('id'));

        if (! $usuario->activo()) {
            $modelo->tokens()->delete();   // CU-12: desactivar revoca el acceso
        }
        $usuario->asignarId($modelo->id);
    }

    private function aDominio(UsuarioModelo $m): Usuario
    {
        return new Usuario(
            $m->id, $m->login, $m->password, $m->activo,
            new Funcionario($m->persona->identificacion, $m->persona->nombre, $m->persona->cargo ?? 'Sin cargo'),
            $m->roles->map(fn (RolModelo $r) => $this->rolADominio($r))->all(),
        );
    }

    private function aModelo(Usuario $u): array
    {
        return ['login' => $u->login(), 'password' => $u->claveHash(), 'activo' => $u->activo()];
    }

    private function rolADominio(RolModelo $r): Rol
    {
        return new Rol($r->nombre, array_map(fn (string $p) => Permiso::from($p), $r->permisos()));
    }
}
