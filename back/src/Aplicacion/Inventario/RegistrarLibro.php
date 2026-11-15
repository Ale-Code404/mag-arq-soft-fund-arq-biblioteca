<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario;

use App\Aplicacion\Inventario\Dto\LibroSalida;
use App\Aplicacion\Inventario\Dto\RegistrarLibroEntrada;
use App\Dominio\Catalogo\Dimensiones;
use App\Dominio\Catalogo\Libro;
use App\Dominio\Catalogo\Tematica;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Repositorios\RepositorioLibros;

/** CU-02 Registrar libro (secuencia D06). */
final class RegistrarLibro
{
    public function __construct(private RepositorioLibros $libros) {}

    public function ejecutar(RegistrarLibroEntrada $e): LibroSalida
    {
        if ($this->libros->existePorIsbn($e->isbn)) {
            throw new ReglaNegocioException('RF-02', 'Ya existe un libro con ese identificador.');
        }
        $tematica = Tematica::tryFrom($e->tematica)
            ?? throw new ReglaNegocioException('RF-02', "La temática {$e->tematica} no existe.");

        $libro = new Libro(
            $e->isbn, $e->titulo, $e->autor, $e->editorial, $e->anio,
            $tematica,
            new Dimensiones($e->altoCm, $e->anchoCm, $e->grosorCm),   // valida rangos (R-02)
        );
        $this->libros->guardar($libro);

        return LibroSalida::desde($libro);
    }
}
