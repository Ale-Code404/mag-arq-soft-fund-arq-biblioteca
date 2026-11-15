<?php

declare(strict_types=1);

namespace App\Aplicacion\Administracion;

use App\Aplicacion\Administracion\Dto\AccionUsuario;
use App\Aplicacion\Administracion\Dto\GestionarUsuariosEntrada;
use App\Aplicacion\Administracion\Dto\UsuarioSalida;
use App\Aplicacion\Puertos\CachePermisos;
use App\Aplicacion\Puertos\HashClaves;
use App\Aplicacion\Puertos\Transaccion;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Personas\Funcionario;
use App\Dominio\Repositorios\RepositorioUsuarios;
use App\Dominio\Seguridad\Rol;
use App\Dominio\Seguridad\Usuario;

/** CU-12 Gestionar usuarios: crear, editar (incluye desactivar) y asignar roles. */
final class GestionarUsuarios
{
    public function __construct(
        private RepositorioUsuarios $usuarios,
        private HashClaves $hash,
        private CachePermisos $cachePermisos,
        private Transaccion $transaccion,
    ) {}

    public function ejecutar(GestionarUsuariosEntrada $e): UsuarioSalida
    {
        $usuario = match ($e->accion) {
            AccionUsuario::CREAR => $this->crear($e),
            AccionUsuario::EDITAR => $this->editar($e),
            AccionUsuario::ASIGNAR_ROLES => $this->asignarRoles($e),
        };

        $this->transaccion->ejecutar(fn () => $this->usuarios->guardar($usuario));
        $this->cachePermisos->invalidar($usuario->id());

        return UsuarioSalida::desde($usuario);
    }

    private function crear(GestionarUsuariosEntrada $e): Usuario
    {
        $login = $e->login ?? '';
        if ($this->usuarios->existeLogin($login)) {
            throw new ReglaNegocioException('RF-12', "El login {$login} ya está en uso.");
        }
        if ($this->usuarios->existeFuncionario($e->identificacion ?? '')) {
            throw new ReglaNegocioException('RF-12', 'Ya existe un funcionario con esa identificación.');
        }

        return new Usuario(
            null,
            $login,
            $this->hash->hashear($e->clave ?? ''),
            true,
            new Funcionario($e->identificacion ?? '', $e->nombre ?? '', $e->cargo ?? ''),
            $this->roles($e->roles ?? []),
        );
    }

    private function editar(GestionarUsuariosEntrada $e): Usuario
    {
        $usuario = $this->buscar($e);
        if ($e->login !== null) {
            if ($this->usuarios->existeLogin($e->login, $usuario->id())) {
                throw new ReglaNegocioException('RF-12', "El login {$e->login} ya está en uso.");
            }
            $usuario->cambiarLogin($e->login);
        }
        if ($e->clave !== null) {
            $usuario->cambiarClave($this->hash->hashear($e->clave));
        }
        if ($e->nombre !== null || $e->cargo !== null) {
            $persona = $usuario->persona();
            $persona->actualizar($e->nombre ?? $persona->getNombre(), $e->cargo ?? $persona->cargo());
        }
        if ($e->activo === true) {
            $usuario->activar();
        }
        if ($e->activo === false) {
            if ($usuario->id() === $e->ejecutadoPor) {
                throw new ReglaNegocioException('RF-12', 'No puede desactivar su propio usuario.');
            }
            $usuario->desactivar();
        }

        return $usuario;
    }

    private function asignarRoles(GestionarUsuariosEntrada $e): Usuario
    {
        $usuario = $this->buscar($e);
        $usuario->asignarRoles($this->roles($e->roles ?? []));

        return $usuario;
    }

    private function buscar(GestionarUsuariosEntrada $e): Usuario
    {
        return $this->usuarios->buscarPorId($e->usuarioId ?? 0)
            ?? throw new RecursoNoEncontradoException("No existe el usuario {$e->usuarioId}.");
    }

    /** @param string[] $nombres @return Rol[] */
    private function roles(array $nombres): array
    {
        $nombres = array_values(array_unique($nombres));
        if ($nombres === []) {
            throw new ReglaNegocioException('RF-12', 'El usuario debe tener al menos un rol.');
        }
        $roles = $this->usuarios->rolesPorNombre($nombres);
        $faltantes = array_diff($nombres, array_map(fn (Rol $r) => $r->nombre(), $roles));
        if ($faltantes !== []) {
            throw new ReglaNegocioException('RF-12', 'Roles inexistentes: '.implode(', ', $faltantes).'.');
        }

        return $roles;
    }
}
