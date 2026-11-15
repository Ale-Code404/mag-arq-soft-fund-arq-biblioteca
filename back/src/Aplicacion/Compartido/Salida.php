<?php

declare(strict_types=1);

namespace App\Aplicacion\Compartido;

/** Toda salida de un caso de uso se puede expresar como arreglo para la API. */
interface Salida
{
    public function aArreglo(): array;
}
