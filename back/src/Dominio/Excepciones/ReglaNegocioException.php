<?php

declare(strict_types=1);

namespace App\Dominio\Excepciones;

use DomainException;

/** Rechazo de una regla de negocio; lleva el código de la regla (RN-03, RF-07...). */
final class ReglaNegocioException extends DomainException
{
    public function __construct(private string $regla, string $mensaje)
    {
        parent::__construct($mensaje);
    }

    public function regla(): string
    {
        return $this->regla;
    }
}
