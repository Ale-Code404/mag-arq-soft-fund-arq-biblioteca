<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Penalizaciones\EstadoMulta;
use App\Dominio\Personas\Lector;
use App\Dominio\Repositorios\RepositorioLectores;
use App\Infraestructura\Persistencia\Modelos\MultaModelo;
use App\Infraestructura\Persistencia\Modelos\PersonaModelo;

class EloquentRepositorioLectores implements RepositorioLectores
{
    public function existePorIdentificacion(string $identificacion): bool
    {
        return $this->consulta($identificacion)->exists();
    }

    public function buscarPorIdentificacion(string $identificacion): ?Lector
    {
        $modelo = $this->consulta($identificacion)->first();

        return $modelo ? $this->aDominio($modelo) : null;
    }

    public function guardar(Lector $lector): void
    {
        PersonaModelo::updateOrCreate(
            ['tipo' => PersonaModelo::LECTOR, 'identificacion' => $lector->identificacion()],
            $this->aModelo($lector),
        );
    }

    private function consulta(string $identificacion)
    {
        return PersonaModelo::where('tipo', PersonaModelo::LECTOR)->where('identificacion', $identificacion);
    }

    private function aDominio(PersonaModelo $m): Lector
    {
        $pendientes = MultaModelo::query()
            ->whereHas('prestamo', fn ($q) => $q->where('lector_id', $m->id))
            ->where('estado', EstadoMulta::PENDIENTE->value)
            ->get()
            ->map(fn (MultaModelo $multa) => TraductorDominio::multa($multa))
            ->all();

        return new Lector($m->identificacion, $m->nombre, $m->direccion, $pendientes);
    }

    private function aModelo(Lector $l): array
    {
        return ['nombre' => $l->getNombre(), 'direccion' => $l->direccion()];
    }
}
