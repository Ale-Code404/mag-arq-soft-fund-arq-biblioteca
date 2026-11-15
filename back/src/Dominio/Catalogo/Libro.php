<?php

declare(strict_types=1);

namespace App\Dominio\Catalogo;

use App\Dominio\Excepciones\ReglaNegocioException;

final class Libro
{
    /** @var Ejemplar[] */
    private array $ejemplares = [];

    public function __construct(
        private string $isbn,
        private string $titulo,
        private string $autor,
        private string $editorial,
        private int $anio,
        private Tematica $tematica,
        private Dimensiones $dimensiones,
    ) {
        if (trim($isbn) === '' || trim($titulo) === '' || trim($autor) === '') {
            throw new ReglaNegocioException('RF-02', 'El ISBN, el título y el autor son obligatorios.');
        }
        if ($anio < 1000 || $anio > 9999) {
            throw new ReglaNegocioException('RF-02', 'El año de publicación no es válido.');
        }
    }

    public function agregarEjemplar(string $codigo): Ejemplar
    {
        foreach ($this->ejemplares as $existente) {
            if ($existente->codigo() === $codigo) {
                throw new ReglaNegocioException('RN-08', "El ejemplar {$codigo} ya existe.");
            }
        }
        $ejemplar = new Ejemplar($codigo, $this);
        $this->ejemplares[] = $ejemplar;

        return $ejemplar;
    }

    public function getGrosorCm(): float
    {
        return $this->dimensiones->grosorCm;
    }

    public function getAltoCm(): float
    {
        return $this->dimensiones->altoCm;
    }

    public function isbn(): string
    {
        return $this->isbn;
    }

    public function titulo(): string
    {
        return $this->titulo;
    }

    public function autor(): string
    {
        return $this->autor;
    }

    public function editorial(): string
    {
        return $this->editorial;
    }

    public function anio(): int
    {
        return $this->anio;
    }

    public function tematica(): Tematica
    {
        return $this->tematica;
    }

    public function dimensiones(): Dimensiones
    {
        return $this->dimensiones;
    }

    /** @return Ejemplar[] */
    public function ejemplares(): array
    {
        return $this->ejemplares;
    }
}
