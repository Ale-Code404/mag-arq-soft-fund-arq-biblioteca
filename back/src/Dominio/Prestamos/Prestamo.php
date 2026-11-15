<?php

declare(strict_types=1);

namespace App\Dominio\Prestamos;

use App\Dominio\Catalogo\Ejemplar;
use App\Dominio\Configuracion\ConfiguracionBiblioteca;
use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Penalizaciones\Multa;
use App\Dominio\Penalizaciones\PoliticaMulta;
use App\Dominio\Personas\Lector;
use DateTimeImmutable;
use ReflectionClass;

final class Prestamo
{
    private ?DateTimeImmutable $fechaDevolucion = null;

    private int $renovaciones = 0;

    private ?Multa $multa = null;

    private DateTimeImmutable $fechaEntregaPrevista;

    public function __construct(
        private ?int $id,
        private Ejemplar $ejemplar,
        private Lector $lector,
        private DateTimeImmutable $fechaPrestamo,
        ConfiguracionBiblioteca $cfg,
    ) {
        if (! $ejemplar->estaDisponible()) {
            throw new ReglaNegocioException('RF-07', 'El ejemplar no está disponible.');
        }
        $this->fechaEntregaPrevista = $fechaPrestamo->modify("+{$cfg->diasPrestamo} days");   // RN-01
        $ejemplar->marcarPrestado();
    }

    /** Reconstruye un préstamo guardado sin repetir los efectos del constructor. */
    public static function reconstituir(
        int $id,
        Ejemplar $ejemplar,
        Lector $lector,
        DateTimeImmutable $fechaPrestamo,
        DateTimeImmutable $fechaEntregaPrevista,
        int $renovaciones,
        ?DateTimeImmutable $fechaDevolucion = null,
        ?Multa $multa = null,
    ): self {
        $p = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();
        $p->id = $id;
        $p->ejemplar = $ejemplar;
        $p->lector = $lector;
        $p->fechaPrestamo = $fechaPrestamo;
        $p->fechaEntregaPrevista = $fechaEntregaPrevista;
        $p->renovaciones = $renovaciones;
        $p->fechaDevolucion = $fechaDevolucion;
        $p->multa = $multa;

        return $p;
    }

    /** RN-02 y RN-03. La nueva fecha se cuenta desde la primera fecha de entrega. */
    public function renovar(DateTimeImmutable $hoy, ConfiguracionBiblioteca $cfg): void
    {
        $this->exigirActivo();
        if ($this->renovaciones >= $cfg->maxRenovaciones) {
            throw new ReglaNegocioException('RN-02', 'El préstamo ya fue renovado.');
        }
        if ($this->estaVencido($hoy)) {
            throw new ReglaNegocioException('RN-03', 'El préstamo está vencido.');
        }
        if ($this->lector->tieneMultasPendientes()) {
            throw new ReglaNegocioException('RN-03', 'El lector tiene multas pendientes.');
        }
        $this->fechaEntregaPrevista = $this->fechaEntregaPrevista->modify("+{$cfg->diasRenovacion} days");
        $this->renovaciones++;
    }

    /** RN-04 y CU-15: la devolución tardía genera una multa con la política recibida. */
    public function devolver(DateTimeImmutable $hoy, PoliticaMulta $politica): ?Multa
    {
        $this->exigirActivo();
        $this->fechaDevolucion = $hoy;
        $this->ejemplar->marcarDisponible();                       // vuelve a su nivel (RN-09)
        $dias = $this->diasRetraso($hoy);
        if ($dias > 0) {
            $valor = $politica->calcular($this, $hoy);              // polimorfismo
            $this->multa = Multa::pendiente($valor, $dias, intdiv($valor, $dias));
        }

        return $this->multa;
    }

    public function diasRetraso(DateTimeImmutable $fecha): int
    {
        if ($fecha <= $this->fechaEntregaPrevista) {
            return 0;
        }

        return (int) $this->fechaEntregaPrevista->diff($fecha)->days;
    }

    public function estaVencido(DateTimeImmutable $hoy): bool
    {
        return $this->estaActivo() && $hoy > $this->fechaEntregaPrevista;
    }

    public function estaActivo(): bool
    {
        return $this->fechaDevolucion === null;
    }

    public function asignarId(int $id): void
    {
        $this->id ??= $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function ejemplar(): Ejemplar
    {
        return $this->ejemplar;
    }

    public function lector(): Lector
    {
        return $this->lector;
    }

    public function fechaPrestamo(): DateTimeImmutable
    {
        return $this->fechaPrestamo;
    }

    public function fechaEntregaPrevista(): DateTimeImmutable
    {
        return $this->fechaEntregaPrevista;
    }

    public function fechaDevolucion(): ?DateTimeImmutable
    {
        return $this->fechaDevolucion;
    }

    public function renovaciones(): int
    {
        return $this->renovaciones;
    }

    public function multa(): ?Multa
    {
        return $this->multa;
    }

    private function exigirActivo(): void
    {
        if (! $this->estaActivo()) {
            throw new ReglaNegocioException('RF-09', 'El préstamo ya fue devuelto.');
        }
    }
}
