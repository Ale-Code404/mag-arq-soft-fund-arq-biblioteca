<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Catalogo\Dimensiones;
use App\Dominio\Catalogo\Ejemplar;
use App\Dominio\Catalogo\EstadoEjemplar;
use App\Dominio\Catalogo\Libro;
use App\Dominio\Catalogo\Tematica;
use App\Dominio\Penalizaciones\EstadoMulta;
use App\Dominio\Penalizaciones\Multa;
use App\Infraestructura\Persistencia\Modelos\EjemplarModelo;
use App\Infraestructura\Persistencia\Modelos\LibroModelo;
use App\Infraestructura\Persistencia\Modelos\MultaModelo;

/** Traducciones de modelo Eloquent a entidad de dominio que comparten varios repositorios. */
final class TraductorDominio
{
    public static function libro(LibroModelo $m): Libro
    {
        return new Libro(
            $m->isbn, $m->titulo, $m->autor, $m->editorial, $m->anio,
            Tematica::from($m->tematica),
            new Dimensiones($m->alto_cm, $m->ancho_cm, $m->grosor_cm),
        );
    }

    public static function ejemplar(EjemplarModelo $m, ?Libro $libro = null): Ejemplar
    {
        return new Ejemplar($m->codigo, $libro ?? self::libro($m->libro), EstadoEjemplar::from($m->estado));
    }

    public static function multa(MultaModelo $m): Multa
    {
        return new Multa(
            $m->id, $m->valor, $m->dias_retraso, $m->tarifa_aplicada,
            EstadoMulta::from($m->estado), $m->fecha_pago,
        );
    }
}
