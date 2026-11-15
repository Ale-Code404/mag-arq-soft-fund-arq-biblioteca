<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Configuracion\ConfiguracionBiblioteca;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Repositorios\RepositorioConfiguracion;
use App\Infraestructura\Persistencia\Modelos\BibliotecaModelo;
use App\Infraestructura\Persistencia\Modelos\ConfiguracionModelo;

class EloquentRepositorioConfiguracion implements RepositorioConfiguracion
{
    public function vigente(): ConfiguracionBiblioteca
    {
        $modelo = ConfiguracionModelo::orderByDesc('vigente_desde')->orderByDesc('id')->first()
            ?? throw new RecursoNoEncontradoException('No hay una configuración vigente.');

        return $this->aDominio($modelo);
    }

    public function guardar(ConfiguracionBiblioteca $configuracion, int $usuarioId): void
    {
        ConfiguracionModelo::create($this->aModelo($configuracion) + [
            'biblioteca_id' => BibliotecaModelo::firstOrFail()->id,
            'vigente_desde' => now(),
            'creado_por' => $usuarioId,
        ]);
    }

    private function aDominio(ConfiguracionModelo $m): ConfiguracionBiblioteca
    {
        return new ConfiguracionBiblioteca(
            $m->dias_prestamo, $m->dias_renovacion, $m->max_renovaciones,
            $m->valor_multa_diaria, $m->holgura_cm, $m->grosor_por_defecto_cm,
        );
    }

    private function aModelo(ConfiguracionBiblioteca $c): array
    {
        return [
            'dias_prestamo' => $c->diasPrestamo,
            'dias_renovacion' => $c->diasRenovacion,
            'max_renovaciones' => $c->maxRenovaciones,
            'valor_multa_diaria' => $c->valorMultaDiaria,
            'holgura_cm' => $c->holguraCm,
            'grosor_por_defecto_cm' => $c->grosorPorDefectoCm,
        ];
    }
}
