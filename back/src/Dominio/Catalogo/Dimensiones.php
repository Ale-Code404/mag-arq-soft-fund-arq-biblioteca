<?php

declare(strict_types=1);

namespace App\Dominio\Catalogo;

use App\Dominio\Excepciones\ReglaNegocioException;

/** Medidas físicas de un libro en centímetros. Rangos supuestos (R-02, P-03). */
final readonly class Dimensiones
{
    public const ALTO_MIN = 5.0;

    public const ALTO_MAX = 60.0;

    public const ANCHO_MIN = 5.0;

    public const ANCHO_MAX = 50.0;

    public const GROSOR_MIN = 0.1;

    public const GROSOR_MAX = 15.0;

    public function __construct(
        public float $altoCm,
        public float $anchoCm,
        public float $grosorCm,
    ) {
        self::validarRango('alto', $altoCm, self::ALTO_MIN, self::ALTO_MAX);
        self::validarRango('ancho', $anchoCm, self::ANCHO_MIN, self::ANCHO_MAX);
        self::validarRango('grosor', $grosorCm, self::GROSOR_MIN, self::GROSOR_MAX);
    }

    private static function validarRango(string $medida, float $valor, float $min, float $max): void
    {
        if ($valor < $min || $valor > $max) {
            throw new ReglaNegocioException('R-02', "El {$medida} debe estar entre {$min} y {$max} cm.");
        }
    }
}
