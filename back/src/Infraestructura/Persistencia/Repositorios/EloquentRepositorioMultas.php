<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Penalizaciones\EstadoMulta;
use App\Dominio\Penalizaciones\Multa;
use App\Dominio\Repositorios\RepositorioMultas;
use App\Infraestructura\Persistencia\Modelos\MultaModelo;
use App\Infraestructura\Persistencia\Modelos\PersonaModelo;

class EloquentRepositorioMultas implements RepositorioMultas
{
    public function lectorTienePendientes(string $identificacion): bool
    {
        return MultaModelo::query()
            ->where('estado', EstadoMulta::PENDIENTE->value)
            ->whereHas('prestamo.lector', fn ($q) => $q
                ->where('tipo', PersonaModelo::LECTOR)
                ->where('identificacion', $identificacion))
            ->exists();
    }

    public function buscarPorId(int $id): ?Multa
    {
        $modelo = MultaModelo::find($id);

        return $modelo ? $this->aDominio($modelo) : null;
    }

    public function agregar(Multa $multa, int $prestamoId): void
    {
        $modelo = MultaModelo::create($this->aModelo($multa) + ['prestamo_id' => $prestamoId]);
        $multa->asignarId($modelo->id);
    }

    public function registrarPago(Multa $multa, int $usuarioId): void
    {
        MultaModelo::whereKey($multa->id())->update($this->aModelo($multa) + ['pago_registrado_por' => $usuarioId]);
    }

    private function aDominio(MultaModelo $m): Multa
    {
        return TraductorDominio::multa($m);
    }

    private function aModelo(Multa $m): array
    {
        return [
            'valor' => $m->valor(),
            'dias_retraso' => $m->diasRetraso(),
            'tarifa_aplicada' => $m->tarifaAplicada(),
            'estado' => $m->estado()->value,
            'fecha_pago' => $m->fechaPago()?->format('Y-m-d'),
        ];
    }
}
