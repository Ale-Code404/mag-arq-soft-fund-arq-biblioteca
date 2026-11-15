<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Catalogo\Libro;
use App\Dominio\Repositorios\RepositorioLibros;
use App\Infraestructura\Persistencia\Modelos\LibroModelo;

class EloquentRepositorioLibros implements RepositorioLibros
{
    public function existePorIsbn(string $isbn): bool
    {
        return LibroModelo::where('isbn', $isbn)->exists();
    }

    public function buscarPorIsbn(string $isbn): ?Libro
    {
        $modelo = LibroModelo::where('isbn', $isbn)->first();

        return $modelo ? $this->aDominio($modelo) : null;
    }

    public function guardar(Libro $libro): void
    {
        LibroModelo::updateOrCreate(['isbn' => $libro->isbn()], $this->aModelo($libro));
    }

    private function aDominio(LibroModelo $m): Libro
    {
        return TraductorDominio::libro($m);
    }

    private function aModelo(Libro $l): array
    {
        return [
            'titulo' => $l->titulo(),
            'autor' => $l->autor(),
            'editorial' => $l->editorial(),
            'anio' => $l->anio(),
            'tematica' => $l->tematica()->value,
            'alto_cm' => $l->dimensiones()->altoCm,
            'ancho_cm' => $l->dimensiones()->anchoCm,
            'grosor_cm' => $l->dimensiones()->grosorCm,
        ];
    }
}
