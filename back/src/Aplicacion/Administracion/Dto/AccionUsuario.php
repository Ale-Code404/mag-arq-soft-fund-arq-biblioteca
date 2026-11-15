<?php

declare(strict_types=1);

namespace App\Aplicacion\Administracion\Dto;

enum AccionUsuario: string
{
    case CREAR = 'CREAR';
    case EDITAR = 'EDITAR';
    case ASIGNAR_ROLES = 'ASIGNAR_ROLES';
}
