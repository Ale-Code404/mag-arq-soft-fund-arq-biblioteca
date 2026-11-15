<?php

declare(strict_types=1);

namespace App\Aplicacion\Prestamos\Dto;

use App\Aplicacion\Compartido\Salida;
use App\Dominio\Prestamos\Prestamo;
use DateTimeImmutable;

final readonly class PrestamoSalida implements Salida
{
    public function __construct(
        public ?int $id,
        public string $codigoEjemplar,
        public string $isbn,
        public string $titulo,
        public string $identificacionLector,
        public string $nombreLector,
        public string $fechaPrestamo,
        public string $fechaEntregaPrevista,
        public ?string $fechaDevolucion,
        public int $renovaciones,
        public bool $activo,
        public bool $vencido,
        public int $diasRetraso,
        public ?MultaSalida $multa,
    ) {}

    public static function desde(Prestamo $p, DateTimeImmutable $hoy): self
    {
        $libro = $p->ejemplar()->libro();

        return new self(
            $p->id(),
            $p->ejemplar()->codigo(),
            $libro->isbn(),
            $libro->titulo(),
            $p->lector()->identificacion(),
            $p->lector()->getNombre(),
            $p->fechaPrestamo()->format('Y-m-d'),
            $p->fechaEntregaPrevista()->format('Y-m-d'),
            $p->fechaDevolucion()?->format('Y-m-d'),
            $p->renovaciones(),
            $p->estaActivo(),
            $p->estaVencido($hoy),
            $p->diasRetraso($p->fechaDevolucion() ?? $hoy),
            $p->multa() ? MultaSalida::desde($p->multa()) : null,
        );
    }

    public function aArreglo(): array
    {
        return array_replace(get_object_vars($this), ['multa' => $this->multa?->aArreglo()]);
    }
}
