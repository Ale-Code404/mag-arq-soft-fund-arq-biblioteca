<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios;

use App\Dominio\Configuracion\ConfiguracionBiblioteca;

interface RepositorioConfiguracion
{
    public function vigente(): ConfiguracionBiblioteca;

    /** Guarda una nueva versión vigente desde ahora; la anterior queda como historial. */
    public function guardar(ConfiguracionBiblioteca $configuracion, int $usuarioId): void;
}
