<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios;

use App\Dominio\Inventario\Biblioteca;
use App\Dominio\Inventario\Estanteria;

interface RepositorioBiblioteca
{
    /** Carga la biblioteca con estanterías, niveles, ejemplares ubicados y libros, y la configuración vigente. */
    public function cargar(): Biblioteca;

    public function guardarEstanteria(Estanteria $estanteria): void;

    public function guardarUbicacion(string $codigoEjemplar, string $codigoEstanteria, int $numeroNivel): void;
}
