<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Personas\Lector;
use App\Dominio\Prestamos\Prestamo;
use App\Dominio\Repositorios\RepositorioLectores;
use App\Dominio\Repositorios\RepositorioPrestamos;
use App\Infraestructura\Persistencia\Modelos\EjemplarModelo;
use App\Infraestructura\Persistencia\Modelos\PersonaModelo;
use App\Infraestructura\Persistencia\Modelos\PrestamoModelo;
use Illuminate\Database\UniqueConstraintViolationException;

class EloquentRepositorioPrestamos implements RepositorioPrestamos
{
    public function __construct(private RepositorioLectores $lectores) {}

    public function buscarActivoPorEjemplar(string $codigoEjemplar): ?Prestamo
    {
        $modelo = PrestamoModelo::with(['ejemplar.libro', 'lector', 'multa'])
            ->whereNull('fecha_devolucion')
            ->whereHas('ejemplar', fn ($q) => $q->where('codigo', $codigoEjemplar))
            ->first();

        return $modelo ? $this->aDominio($modelo, $this->lectores->buscarPorIdentificacion($modelo->lector->identificacion)) : null;
    }

    public function guardar(Prestamo $prestamo, int $usuarioId): void
    {
        if ($prestamo->id() !== null) {
            PrestamoModelo::whereKey($prestamo->id())->update($this->aModelo($prestamo));

            return;
        }

        try {
            $modelo = PrestamoModelo::create($this->aModelo($prestamo) + [
                'ejemplar_id' => EjemplarModelo::where('codigo', $prestamo->ejemplar()->codigo())->value('id'),
                'lector_id' => PersonaModelo::where('tipo', PersonaModelo::LECTOR)
                    ->where('identificacion', $prestamo->lector()->identificacion())->value('id'),
                'fecha_prestamo' => $prestamo->fechaPrestamo()->format('Y-m-d'),
                'registrado_por' => $usuarioId,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Índice único parcial prestamo_activo_unico: otra solicitud prestó el ejemplar primero
            throw new ReglaNegocioException('R-04', 'El ejemplar ya tiene un préstamo activo.');
        }
        $prestamo->asignarId($modelo->id);
    }

    public function historialDeLector(string $identificacion): array
    {
        $lector = $this->lectores->buscarPorIdentificacion($identificacion);
        if ($lector === null) {
            return [];
        }

        return PrestamoModelo::with(['ejemplar.libro', 'multa'])
            ->whereHas('lector', fn ($q) => $q->where('tipo', PersonaModelo::LECTOR)->where('identificacion', $identificacion))
            ->orderByDesc('fecha_prestamo')
            ->orderByDesc('id')
            ->get()
            ->map(fn (PrestamoModelo $m) => $this->aDominio($m, $lector))
            ->all();
    }

    private function aDominio(PrestamoModelo $m, Lector $lector): Prestamo
    {
        return Prestamo::reconstituir(
            $m->id,
            TraductorDominio::ejemplar($m->ejemplar),
            $lector,
            $m->fecha_prestamo,
            $m->fecha_entrega_prevista,
            $m->renovaciones,
            $m->fecha_devolucion,
            $m->multa ? TraductorDominio::multa($m->multa) : null,
        );
    }

    private function aModelo(Prestamo $p): array
    {
        return [
            'fecha_entrega_prevista' => $p->fechaEntregaPrevista()->format('Y-m-d'),
            'fecha_devolucion' => $p->fechaDevolucion()?->format('Y-m-d'),
            'renovaciones' => $p->renovaciones(),
        ];
    }
}
