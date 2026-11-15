<?php

namespace App\Infraestructura\Persistencia\Repositorios;

use App\Dominio\Catalogo\Libro;
use App\Dominio\Inventario\Biblioteca;
use App\Dominio\Inventario\Estanteria;
use App\Dominio\Inventario\Nivel;
use App\Dominio\Repositorios\RepositorioBiblioteca;
use App\Dominio\Repositorios\RepositorioConfiguracion;
use App\Infraestructura\Persistencia\Modelos\BibliotecaModelo;
use App\Infraestructura\Persistencia\Modelos\EjemplarModelo;
use App\Infraestructura\Persistencia\Modelos\EstanteriaModelo;
use App\Infraestructura\Persistencia\Modelos\NivelModelo;

class EloquentRepositorioBiblioteca implements RepositorioBiblioteca
{
    public function __construct(private RepositorioConfiguracion $configuracion) {}

    /** RNF-08: una consulta por relación gracias a with(). */
    public function cargar(): Biblioteca
    {
        $modelo = BibliotecaModelo::with('estanterias.niveles.ejemplares.libro')->firstOrFail();

        return $this->aDominio($modelo);
    }

    public function guardarEstanteria(Estanteria $estanteria): void
    {
        $modelo = EstanteriaModelo::create([
            'biblioteca_id' => BibliotecaModelo::firstOrFail()->id,
            'codigo' => $estanteria->codigo(),
        ]);
        foreach ($estanteria->niveles() as $n) {
            $modelo->niveles()->create(['numero' => $n->numero(), 'largo_cm' => $n->largoCm(), 'alto_cm' => $n->altoCm()]);
        }
    }

    public function guardarUbicacion(string $codigoEjemplar, string $codigoEstanteria, int $numeroNivel): void
    {
        $nivelId = NivelModelo::query()
            ->join('estanterias', 'estanterias.id', '=', 'niveles.estanteria_id')
            ->where('estanterias.codigo', $codigoEstanteria)
            ->where('niveles.numero', $numeroNivel)
            ->value('niveles.id');

        EjemplarModelo::where('codigo', $codigoEjemplar)->update(['nivel_id' => $nivelId]);
    }

    private function aDominio(BibliotecaModelo $m): Biblioteca
    {
        /** @var array<int, Libro> $libros un mismo libro se comparte entre sus ejemplares */
        $libros = [];
        $estanterias = [];
        foreach ($m->estanterias as $e) {
            $niveles = [];
            foreach ($e->niveles as $n) {
                $ejemplares = [];
                foreach ($n->ejemplares as $ej) {
                    $libros[$ej->libro_id] ??= TraductorDominio::libro($ej->libro);
                    $ejemplares[] = TraductorDominio::ejemplar($ej, $libros[$ej->libro_id]);
                }
                $niveles[] = new Nivel($n->numero, $n->largo_cm, $n->alto_cm, $ejemplares);
            }
            $estanterias[] = new Estanteria($e->codigo, $niveles);
        }

        return new Biblioteca($m->nombre, $this->configuracion->vigente(), $estanterias);
    }
}
