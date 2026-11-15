<?php

declare(strict_types=1);

namespace App\Aplicacion\Inventario;

use App\Aplicacion\Inventario\Dto\EjemplarSalida;
use App\Aplicacion\Inventario\Dto\RegistrarEjemplarEntrada;
use App\Aplicacion\Inventario\Dto\UbicarEjemplarEntrada;
use App\Aplicacion\Puertos\Transaccion;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Repositorios\RepositorioEjemplares;
use App\Dominio\Repositorios\RepositorioLibros;

/** CU-03 Registrar ejemplar y ubicarlo (incluye CU-04) en una sola transacción. */
final class RegistrarEjemplar
{
    public function __construct(
        private RepositorioLibros $libros,
        private RepositorioEjemplares $ejemplares,
        private UbicarEjemplar $ubicarEjemplar,
        private Transaccion $transaccion,
    ) {}

    public function ejecutar(RegistrarEjemplarEntrada $e): EjemplarSalida
    {
        $libro = $this->libros->buscarPorIsbn($e->isbn)
            ?? throw new RecursoNoEncontradoException("No existe un libro con ISBN {$e->isbn}.");
        if ($this->ejemplares->existePorCodigo($e->codigo)) {
            throw new ReglaNegocioException('RN-08', "Ya existe un ejemplar con el código {$e->codigo}.");
        }

        return $this->transaccion->ejecutar(function () use ($libro, $e) {
            $this->ejemplares->guardar($libro->agregarEjemplar($e->codigo));

            // Si no cabe en el nivel, la excepción deshace también el registro del ejemplar
            return $this->ubicarEjemplar->ejecutar(new UbicarEjemplarEntrada($e->codigo, $e->estanteria, $e->nivel, $e->usuarioId));
        });
    }
}
