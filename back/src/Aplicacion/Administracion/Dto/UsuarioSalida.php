<?php

declare(strict_types=1);

namespace App\Aplicacion\Administracion\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Seguridad\Rol;
use App\Dominio\Seguridad\Usuario;

final readonly class UsuarioSalida implements Salida
{
    /** @param string[] $roles */
    public function __construct(
        public ?int $id,
        public string $login,
        public bool $activo,
        public string $identificacion,
        public string $nombre,
        public string $cargo,
        public array $roles,
    ) {}

    public static function desde(Usuario $u): self
    {
        $p = $u->persona();

        return new self($u->id(), $u->login(), $u->activo(), $p->identificacion(), $p->getNombre(), $p->cargo(), array_map(fn (Rol $r) => $r->nombre(), $u->roles()));
    }

    public function aArreglo(): array
    {
        return get_object_vars($this);
    }
}
