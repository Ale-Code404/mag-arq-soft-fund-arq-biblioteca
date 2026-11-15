<?php

declare(strict_types=1);

namespace App\Aplicacion\Administracion\Dto;

final readonly class GestionarUsuariosEntrada
{
    public function __construct(
        public AccionUsuario $accion,
        public int $ejecutadoPor,
        public ?int $usuarioId = null,
        public ?string $login = null,
        public ?string $clave = null,
        public ?string $identificacion = null,
        public ?string $nombre = null,
        public ?string $cargo = null,
        public ?bool $activo = null,
        /** @var string[]|null nombres de rol */
        public ?array $roles = null,
    ) {}
}
