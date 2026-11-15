<?php

declare(strict_types=1);

namespace App\Dominio\Inventario;

use App\Dominio\Catalogo\Ejemplar;
use App\Dominio\Catalogo\Tematica;
use App\Dominio\Configuracion\ConfiguracionBiblioteca;
use App\Dominio\Excepciones\RecursoNoEncontradoException;
use App\Dominio\Excepciones\ReglaNegocioException;

/** Raíz del inventario físico: Biblioteca → Estanteria → Nivel → Ejemplar (composición). */
final class Biblioteca
{
    /** @var Estanteria[] */
    private array $estanterias = [];

    /** @param Estanteria[] $estanterias */
    public function __construct(
        private string $nombre,
        private ConfiguracionBiblioteca $configuracion,
        array $estanterias = [],
    ) {
        foreach ($estanterias as $e) {
            $this->agregarEstanteria($e);
        }
    }

    public function agregarEstanteria(Estanteria $e): void
    {
        foreach ($this->estanterias as $existente) {
            if ($existente->codigo() === $e->codigo()) {
                throw new ReglaNegocioException('RF-01', "Ya existe la estantería {$e->codigo()}.");
            }
        }
        $this->estanterias[] = $e;
    }

    public function capacidadInstalada(): float
    {
        return array_sum(array_map(fn (Estanteria $e) => $e->capacidadInstalada(), $this->estanterias));
    }

    public function capacidadUsada(): float
    {
        $h = $this->configuracion->holguraCm;

        return array_sum(array_map(fn (Estanteria $e) => $e->espacioUsado($h), $this->estanterias));
    }

    public function capacidadDisponible(): float
    {
        return $this->capacidadInstalada() - $this->capacidadUsada();
    }

    // Sobrecarga en PHP: un parámetro opcional reemplaza dos firmas
    // grosorPromedio() para toda la biblioteca y grosorPromedio($tematica) para una temática (RN-10)
    public function grosorPromedio(?Tematica $t = null): float
    {
        $grosores = $this->grosores($t);

        return $grosores === []
            ? $this->configuracion->grosorPorDefectoCm
            : array_sum($grosores) / count($grosores);
    }

    public function usaGrosorPorDefecto(?Tematica $t = null): bool
    {
        return $this->grosores($t) === [];
    }

    /** DA-13: libros que caben en la capacidad instalada con el grosor promedio. */
    public function capacidadTeorica(?Tematica $t = null): int
    {
        return self::librosQueCaben($this->capacidadInstalada(), $this->grosorPromedio($t) + $this->configuracion->holguraCm);
    }

    /** Libros que todavía caben en el espacio disponible. */
    public function capacidadDisponibleLibros(?Tematica $t = null): int
    {
        return self::librosQueCaben($this->capacidadDisponible(), $this->grosorPromedio($t) + $this->configuracion->holguraCm);
    }

    /** CU-04: ubica (o reubica) un ejemplar en un nivel aplicando RN-06 y RN-07. */
    public function ubicar(Ejemplar $ejemplar, string $codigoEstanteria, int $numeroNivel): void
    {
        $destino = $this->estanteria($codigoEstanteria)->nivel($numeroNivel);
        $origen = $this->nivelDe($ejemplar->codigo());
        $origen?->retirar($ejemplar->codigo());

        try {
            $destino->ubicar($ejemplar, $this->configuracion->holguraCm);
        } catch (ReglaNegocioException $e) {
            $origen?->ubicar($ejemplar, $this->configuracion->holguraCm);
            throw $e;
        }
    }

    public function estanteria(string $codigo): Estanteria
    {
        foreach ($this->estanterias as $e) {
            if ($e->codigo() === $codigo) {
                return $e;
            }
        }
        throw new RecursoNoEncontradoException("La estantería {$codigo} no existe.");
    }

    /** Vista de la biblioteca reducida a una estantería, para medir su capacidad con las mismas fórmulas. */
    public function soloEstanteria(string $codigo): self
    {
        return new self($this->nombre, $this->configuracion, [$this->estanteria($codigo)]);
    }

    public function nombre(): string
    {
        return $this->nombre;
    }

    public function configuracion(): ConfiguracionBiblioteca
    {
        return $this->configuracion;
    }

    /** @return Estanteria[] */
    public function estanterias(): array
    {
        return $this->estanterias;
    }

    private function nivelDe(string $codigoEjemplar): ?Nivel
    {
        foreach ($this->estanterias as $e) {
            foreach ($e->niveles() as $n) {
                if ($n->contiene($codigoEjemplar)) {
                    return $n;
                }
            }
        }

        return null;
    }

    /** @return float[] */
    private function grosores(?Tematica $t): array
    {
        $grosores = [];
        foreach ($this->ejemplaresUbicados() as $ej) {
            if ($t === null || $ej->libro()->tematica() === $t) {
                $grosores[] = $ej->libro()->getGrosorCm();
            }
        }

        return $grosores;
    }

    /** @return iterable<Ejemplar> */
    private function ejemplaresUbicados(): iterable
    {
        foreach ($this->estanterias as $e) {
            foreach ($e->niveles() as $n) {
                yield from $n->ejemplares();
            }
        }
    }

    /** RNF-05: libros siempre hacia abajo; se redondea antes para evitar 48,999... por decimales. */
    private static function librosQueCaben(float $espacioCm, float $espacioPorLibroCm): int
    {
        return max(0, (int) floor(round($espacioCm / $espacioPorLibroCm, 6)));
    }
}
