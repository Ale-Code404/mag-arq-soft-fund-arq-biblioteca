<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Catalogo\Ejemplar;
use App\Dominio\Repositorios\RepositorioEjemplares;
use App\Infraestructura\Persistencia\Modelos\EjemplarModelo;
use App\Infraestructura\Persistencia\Modelos\LibroModelo;

class EloquentRepositorioEjemplares implements RepositorioEjemplares
{
    public function existePorCodigo(string $codigo): bool
    {
        return EjemplarModelo::where('codigo', $codigo)->exists();
    }

    public function buscarPorCodigo(string $codigo): ?Ejemplar
    {
        $modelo = EjemplarModelo::with('libro')->where('codigo', $codigo)->first();

        return $modelo ? $this->aDominio($modelo) : null;
    }

    public function guardar(Ejemplar $ejemplar): void
    {
        EjemplarModelo::updateOrCreate(['codigo' => $ejemplar->codigo()], $this->aModelo($ejemplar));
    }

    public function ubicacionDe(string $codigo): ?array
    {
        $fila = EjemplarModelo::query()
            ->join('niveles', 'niveles.id', '=', 'ejemplares.nivel_id')
            ->join('estanterias', 'estanterias.id', '=', 'niveles.estanteria_id')
            ->where('ejemplares.codigo', $codigo)
            ->first(['estanterias.codigo as estanteria', 'niveles.numero as nivel']);

        return $fila ? ['estanteria' => $fila->estanteria, 'nivel' => (int) $fila->nivel] : null;
    }

    private function aDominio(EjemplarModelo $m): Ejemplar
    {
        return TraductorDominio::ejemplar($m);
    }

    private function aModelo(Ejemplar $e): array
    {
        return [
            'libro_id' => LibroModelo::where('isbn', $e->libro()->isbn())->value('id'),
            'estado' => $e->estado()->value,
        ];
    }
}
