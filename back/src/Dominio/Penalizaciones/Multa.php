<?php

declare(strict_types=1);

namespace App\Dominio\Penalizaciones;

use App\Dominio\Excepciones\ReglaNegocioException;
use DateTimeImmutable;

final class Multa
{
    public function __construct(
        private ?int $id,
        private int $valor,
        private int $diasRetraso,
        private int $tarifaAplicada,
        private EstadoMulta $estado = EstadoMulta::PENDIENTE,
        private ?DateTimeImmutable $fechaPago = null,
    ) {
        if ($valor <= 0 || $diasRetraso <= 0 || $tarifaAplicada <= 0) {
            throw new ReglaNegocioException('RN-04', 'La multa debe tener valor, días de retraso y tarifa positivos.');
        }
        if (($estado === EstadoMulta::PAGADA) !== ($fechaPago !== null)) {
            throw new ReglaNegocioException('RN-04', 'Una multa pagada debe tener fecha de pago y una pendiente no.');
        }
    }

    /** CU-15: multa nueva por devolución tardía. */
    public static function pendiente(int $valor, int $diasRetraso, int $tarifaAplicada): self
    {
        return new self(null, $valor, $diasRetraso, $tarifaAplicada);
    }

    public function marcarPagada(DateTimeImmutable $fecha): void
    {
        if (! $this->estaPendiente()) {
            throw new ReglaNegocioException('RF-10', 'La multa ya fue pagada.');
        }
        $this->estado = EstadoMulta::PAGADA;
        $this->fechaPago = $fecha;
    }

    public function estaPendiente(): bool
    {
        return $this->estado === EstadoMulta::PENDIENTE;
    }

    public function asignarId(int $id): void
    {
        $this->id ??= $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function valor(): int
    {
        return $this->valor;
    }

    public function diasRetraso(): int
    {
        return $this->diasRetraso;
    }

    public function tarifaAplicada(): int
    {
        return $this->tarifaAplicada;
    }

    public function estado(): EstadoMulta
    {
        return $this->estado;
    }

    public function fechaPago(): ?DateTimeImmutable
    {
        return $this->fechaPago;
    }
}
